<?php

namespace App\Http\Controllers;

use App\Models\PremiumPayment;
use App\Services\GeniusPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming GeniusPay webhook events.
     *
     * POST /api/premium/geniuspay-webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Webhook-Signature', '');
        $timestamp = $request->header('X-Webhook-Timestamp', '');
        $event     = $request->header('X-Webhook-Event', '');
        $rawBody   = $request->getContent();

        if (empty($signature) || empty($timestamp)) {
            Log::warning('[Webhook] Missing signature or timestamp headers');
            return response()->json(['status' => 'error', 'message' => 'Missing authentication headers'], 401);
        }

        $geniusPay = new GeniusPayService();

        // 1. Verify timestamp freshness (replay attack protection — 5 min window)
        if (! $geniusPay->isTimestampFresh($timestamp)) {
            Log::warning('[Webhook] Timestamp too old', ['timestamp' => $timestamp]);
            return response()->json(['status' => 'error', 'message' => 'Timestamp too old'], 400);
        }

        // 2. Verify HMAC-SHA256 signature
        if (! $geniusPay->verifyWebhookSignature($rawBody, $timestamp, $signature)) {
            Log::warning('[Webhook] Invalid signature', ['event' => $event]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $payload  = $request->all();
        $data     = $payload['data'] ?? [];
        $anonId   = $data['metadata']['anon_id'] ?? null;
        $reference = $data['reference'] ?? null;

        Log::info('[Webhook] Event received', [
            'event'     => $event,
            'reference' => $reference,
            'anon_id'   => $anonId,
        ]);

        // 3. Route event
        switch ($event) {
            case 'payment.success':
                $this->handlePaymentSuccess($reference, $anonId, $data);
                break;

            case 'payment.failed':
            case 'payment.cancelled':
            case 'payment.expired':
                $this->handlePaymentFailure($reference, $event);
                break;

            default:
                // Unhandled event — acknowledge anyway
                Log::info('[Webhook] Unhandled event', ['event' => $event]);
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function handlePaymentSuccess(
        ?string $reference,
        ?string $anonId,
        array   $data
    ): void {
        if (! $reference) {
            Log::error('[Webhook] payment.success missing reference');
            return;
        }

        // Find by GeniusPay reference
        $payment = PremiumPayment::where('geniuspay_reference', $reference)->first();

        if (! $payment) {
            // Fallback: try to match by payment_id
            $payment = PremiumPayment::where('payment_id', $reference)->first();
        }

        if (! $payment) {
            Log::error('[Webhook] payment.success — no matching PremiumPayment', [
                'reference' => $reference,
                'anon_id'   => $anonId,
            ]);
            return;
        }

        $payment->update([
            'status'      => 'success',
            'verified_at' => now(),
        ]);

        Log::info('[Webhook] Premium activated', [
            'anon_id'   => $payment->anon_id,
            'plan'      => $payment->plan,
            'reference' => $reference,
        ]);
    }

    private function handlePaymentFailure(?string $reference, string $event): void
    {
        if (! $reference) {
            return;
        }

        $payment = PremiumPayment::where('geniuspay_reference', $reference)
            ->orWhere('payment_id', $reference)
            ->first();

        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => 'failed']);
            Log::info('[Webhook] Payment failed/cancelled', [
                'event'     => $event,
                'reference' => $reference,
            ]);
        }
    }
}
