<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Quote;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Support\PrivacyLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Customer-facing PDF documents (dompdf, DejaVu Sans, pt-BR). */
final class PdfService
{
    public const FAILURE_MESSAGE = 'Não foi possível gerar o PDF agora. Tente novamente.';

    private const LOGO_MAX_BYTES = 2 * 1024 * 1024;

    public function quote(Quote $quote, User $user): Response
    {
        $quote->load(['items.product', 'client', 'account']);

        return $this->render('quote', $quote->id, 'pdf.quote', [
            'quote' => $quote,
            'logo' => $this->logoDataUri($quote->account),
        ], "orcamento-{$quote->number}.pdf", $user, $quote);
    }

    public function serviceOrder(ServiceOrder $order, User $user): Response
    {
        $order->load(['client', 'quote', 'account']);

        return $this->render('service_order', $order->id, 'pdf.service-order', [
            'order' => $order,
            'logo' => $this->logoDataUri($order->account),
        ], "ordem-de-servico-{$order->number}.pdf", $user, $order);
    }

    /** The logo is embedded as a data URI, so dompdf never fetches remote files. */
    public function logoDataUri(?Account $account): ?string
    {
        $logo = $account?->getFirstMedia('logo');

        if ($logo === null || !is_file($logo->getPath()) || $logo->size > self::LOGO_MAX_BYTES) {
            return null;
        }

        $contents = file_get_contents($logo->getPath());

        return $contents === false ? null : 'data:'.$logo->mime_type.';base64,'.base64_encode($contents);
    }

    /** @param  array<string, mixed>  $data */
    private function render(string $type, string $id, string $view, array $data, string $fileName, User $user, Quote|ServiceOrder $subject): Response
    {
        try {
            $response = Pdf::loadView($view, $data)->setPaper('a4')->download($fileName);
        } catch (Throwable $e) {
            Log::error('PDF generation failed', ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);

            return back()->with('error', self::FAILURE_MESSAGE);
        }

        PrivacyLog::record('pdf.generated', 'PDF gerado', $user, $subject, ['type' => $type, 'id' => $id]);

        return $response;
    }
}
