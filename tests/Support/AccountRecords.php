<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\AccountInvitation;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Deal;
use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Models\Interaction;
use App\Models\LeadForm;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quote;
use App\Models\Reminder;
use App\Models\ServiceOrder;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One record of each account-owned type, created for a given owner. Call it
 * before actingAs() (or for the acting user's own account): BelongsToAccount
 * forces the authenticated user's account on create.
 */
final class AccountRecords
{
    /** Route parameter name => record maker. */
    public static function make(string $parameter, User $owner): Model
    {
        $account = (string) $owner->account_id;
        $base = ['account_id' => $account, 'user_id' => $owner->id];

        return match ($parameter) {
            'client' => Client::factory()->create($base),
            'appointment' => Appointment::factory()->forAccount($account)->create(['user_id' => $owner->id]),
            'service_order' => ServiceOrder::factory()->forAccount($account)->create(['user_id' => $owner->id]),
            'purchase' => Purchase::factory()->forAccount($account)->create(['user_id' => $owner->id]),
            'product' => Product::factory()->forAccount($account)->create(['user_id' => $owner->id]),
            'quote' => Quote::factory()->forAccount($account)->create([
                'user_id' => $owner->id,
                'client_id' => Client::factory()->create($base)->id,
            ]),
            'deal' => Deal::factory()->forAccount($account)->create([
                'user_id' => $owner->id,
                'client_id' => Client::factory()->create($base)->id,
                'pipeline_stage_id' => self::make('stage', $owner)->getKey(),
            ]),
            'stage' => PipelineStage::create($base + ['name' => 'Etapa '.Str::random(4), 'color' => '#6366f1', 'position' => 1, 'probability' => 10]),
            'activity' => Activity::create($base + [
                'client_id' => Client::factory()->create($base)->id,
                'type' => 'note', 'title' => 'Atividade', 'happened_at' => now(),
            ]),
            'tag' => Tag::create($base + ['name' => 'Tag '.Str::random(4), 'color' => '#ff0000']),
            'reminder' => Reminder::create($base + ['title' => 'Lembrete', 'remind_at' => now()->addDay()]),
            'interaction' => Interaction::create($base + [
                'client_id' => Client::factory()->create($base)->id,
                'type' => 'support', 'channel' => 'email', 'subject' => 'Chamado', 'status' => 'open',
            ]),
            'entry' => FinancialEntry::factory()->forAccount($account)->create(['user_id' => $owner->id]),
            'category' => FinancialCategory::create($base + ['name' => 'Categoria '.Str::random(4), 'type' => 'income', 'color' => '#10b981']),
            'lead_form' => LeadForm::create($base + [
                'name' => 'Form', 'slug' => 'form-'.Str::lower(Str::random(8)), 'title' => 'Fale conosco',
                'fields' => [['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true]],
            ]),
            'invitation' => AccountInvitation::create([
                'account_id' => $account, 'email' => Str::lower(Str::random(8)).'@example.com',
                'role' => User::ROLE_COLLABORATOR, 'invited_by' => $owner->id,
            ]),
            'member' => Personas::collaborator($owner->account),
            default => throw new \InvalidArgumentException("No record maker for {$parameter}."),
        };
    }
}
