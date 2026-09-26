<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\LeadForm;
use App\Models\LeadFormSubmission;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Processa o envio do formulário público. Roda SEM usuário logado, por isso
 * todo `user_id` é definido explicitamente e as consultas removem o escopo global.
 */
final class LeadCaptureService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {}

    /**
     * @param  array<string, mixed>  $data  valores já validados, indexados pelo nome do campo
     * @param  array<string, ?string>  $meta  ip, user_agent, referrer, utm_*
     */
    public function handle(LeadForm $form, array $data, array $meta = []): LeadFormSubmission
    {
        return DB::transaction(function () use ($form, $data, $meta): LeadFormSubmission {
            $owner = User::findOrFail($form->user_id);
            $client = null;
            $deal = null;
            $note = null;

            $email = isset($data['email']) ? mb_strtolower(trim((string) $data['email'])) : null;

            // Deduplicação: mesmo e-mail = mesmo cliente (evita cadastro repetido a cada envio)
            if ($email) {
                $client = Client::withoutGlobalScope('owned')
                    ->where('user_id', $owner->id)
                    ->where('email', $email)
                    ->first();
            }

            if ($client === null) {
                if ($this->subscriptions->canCreate($owner, 'clients')) {
                    $client = new Client([
                        'name' => $data['name'] ?? ($email ?: 'Lead ' . now()->format('d/m H:i')),
                        'email' => $email,
                        'phone' => $data['phone'] ?? null,
                        'city' => $data['city'] ?? null,
                        'source' => array_key_exists($form->default_source, Client::SOURCES) ? $form->default_source : 'site',
                        'notes' => $this->buildNotes($form, $data),
                    ]);
                    $client->user_id = $owner->id;
                    $client->save();
                } else {
                    // O lead NÃO se perde: fica registrado no envio para o dono ver e fazer upgrade
                    $note = 'Limite de clientes do plano atingido — cliente não foi criado automaticamente.';
                }
            }

            if ($client !== null && $form->create_deal) {
                PipelineStage::ensureDefaultsFor($owner->id);

                $stageId = $form->pipeline_stage_id
                    ?? PipelineStage::withoutGlobalScope('owned')->where('user_id', $owner->id)->where('is_active', true)->orderBy('position')->value('id');
                $probability = (int) (PipelineStage::withoutGlobalScope('owned')->whereKey($stageId)->value('probability') ?? 10);

                $deal = new Deal([
                    'client_id' => $client->id,
                    'pipeline_stage_id' => $stageId,
                    'title' => 'Lead: ' . ($data['company'] ?? $client->name),
                    'description' => $data['message'] ?? null,
                    'value' => 0,
                    'probability' => $probability,
                    'status' => Deal::STATUS_OPEN,
                    'source' => $form->default_source,
                ]);
                $deal->user_id = $owner->id;
                $deal->save();
            }

            if ($client !== null) {
                $activity = new Activity([
                    'client_id' => $client->id,
                    'deal_id' => $deal?->id,
                    'type' => 'note',
                    'title' => "Lead captado pelo formulário \"{$form->name}\"",
                    'description' => $this->buildNotes($form, $data),
                    'happened_at' => now(),
                ]);
                $activity->user_id = $owner->id;
                $activity->save();
            }

            $submission = new LeadFormSubmission([
                'lead_form_id' => $form->id,
                'client_id' => $client?->id,
                'deal_id' => $deal?->id,
                'data' => $data,
                'ip' => $meta['ip'] ?? null,
                'user_agent' => isset($meta['user_agent']) ? mb_substr((string) $meta['user_agent'], 0, 255) : null,
                'referrer' => isset($meta['referrer']) ? mb_substr((string) $meta['referrer'], 0, 500) : null,
                'utm_source' => $meta['utm_source'] ?? null,
                'utm_medium' => $meta['utm_medium'] ?? null,
                'utm_campaign' => $meta['utm_campaign'] ?? null,
                'note' => $note,
            ]);
            $submission->user_id = $owner->id;
            $submission->save();

            LeadForm::withoutGlobalScope('owned')->whereKey($form->id)->increment('submissions_count');

            return $submission;
        });
    }

    private function buildNotes(LeadForm $form, array $data): string
    {
        $labels = collect($form->fields)->pluck('label', 'name');

        return collect($data)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v, $k) => ($labels[$k] ?? $k) . ': ' . $v)
            ->implode("\n");
    }
}
