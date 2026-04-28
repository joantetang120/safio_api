<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeniusPayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl       = config('geniuspay.base_url');
        $this->apiKey        = config('geniuspay.api_key');
        $this->apiSecret     = config('geniuspay.api_secret');
        $this->webhookSecret = config('geniuspay.webhook_secret');
    }

    /**
     * Create a GeniusPay checkout payment.
     *
     * Returns an array with keys: reference, checkout_url, status
     * or throws an exception on failure.
     */
    public function initiatePayment(
        string $anonId,
        int    $amount,
        string $plan,
        string $description
    ): array {
        $response = Http::withHeaders([
            'X-API-Key'    => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post("{$this->baseUrl}/payments", [
            'amount'      => $amount,
            'currency'    => 'XOF',
            'description' => $description,
            'success_url' => config('geniuspay.success_url'),
            'error_url'   => config('geniuspay.error_url'),
            'metadata'    => [
                'anon_id' => $anonId,
                'plan'    => $plan,
            ],
        ]);

        if (! $response->successful()) {
            Log::error('[GeniusPay] Payment initiation failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'anon_id' => $anonId,
            ]);
            throw new \RuntimeException('GeniusPay payment initiation failed: ' . $response->body());
        }

        $data = $response->json('data');

        return [
            'reference'    => $data['reference'],
            'checkout_url' => $data['checkout_url'] ?? $data['payment_url'],
            'status'       => $data['status'],
        ];
    }

    /**
     * Verify the HMAC-SHA256 signature of an incoming GeniusPay webhook.
     *
     * Signature = HMAC-SHA256(timestamp . "." . json_payload, webhook_secret)
     */
    public function verifyWebhookSignature(
        string $rawPayload,
        string $timestamp,
        string $signature
    ): bool {
        $expected = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Check if the webhook timestamp is fresh (within 5 minutes).
     */
    public function isTimestampFresh(string $timestamp): bool
    {
        return abs(time() - (int) $timestamp) <= 300;
    }
}
