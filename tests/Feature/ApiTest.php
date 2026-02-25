<?php

namespace Tests\Feature;

use App\Models\PremiumPayment;
use App\Models\Rule;
use App\Models\Snapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_upload_creates_new_snapshot(): void
    {
        $payload = [
            'anon_id' => 'test-anon-123',
            'encrypted_blob' => 'encrypted_data_here',
            'schema_version' => '1.0',
            'checksum' => 'abc123def456',
            'last_sync' => '2026-02-02T12:00:00Z',
        ];

        $response = $this->postJson('/api/snapshots/upload', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Snapshot uploaded successfully',
            ]);

        $this->assertDatabaseHas('snapshots', [
            'anon_id' => 'test-anon-123',
            'checksum' => 'abc123def456',
        ]);
    }

    public function test_snapshot_upload_updates_existing_snapshot(): void
    {
        Snapshot::create([
            'anon_id' => 'test-anon-123',
            'encrypted_blob' => 'old_data',
            'schema_version' => '1.0',
            'checksum' => 'old_checksum',
            'last_sync' => '2026-02-01T12:00:00Z',
        ]);

        $payload = [
            'anon_id' => 'test-anon-123',
            'encrypted_blob' => 'new_encrypted_data',
            'schema_version' => '1.1',
            'checksum' => 'new_checksum',
            'last_sync' => '2026-02-02T12:00:00Z',
        ];

        $response = $this->postJson('/api/snapshots/upload', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('snapshots', [
            'anon_id' => 'test-anon-123',
            'checksum' => 'new_checksum',
        ]);

        $this->assertDatabaseMissing('snapshots', [
            'checksum' => 'old_checksum',
        ]);
    }

    public function test_snapshot_upload_validation_fails_with_missing_fields(): void
    {
        $payload = [
            'anon_id' => 'test-anon-123',
        ];

        $response = $this->postJson('/api/snapshots/upload', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ]);
    }

    public function test_snapshot_fetch_returns_snapshot(): void
    {
        Snapshot::create([
            'anon_id' => 'test-anon-123',
            'encrypted_blob' => 'encrypted_data_here',
            'schema_version' => '1.0',
            'checksum' => 'abc123def456',
            'last_sync' => '2026-02-02T12:00:00Z',
        ]);

        $response = $this->getJson('/api/snapshots/fetch/test-anon-123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'anon_id',
                'encrypted_blob',
                'schema_version',
                'checksum',
                'last_sync',
            ])
            ->assertJson([
                'anon_id' => 'test-anon-123',
                'checksum' => 'abc123def456',
            ]);
    }

    public function test_snapshot_fetch_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/snapshots/fetch/non-existent-id');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Snapshot not found',
            ]);
    }

    public function test_premium_verify_creates_payment_record(): void
    {
        $payload = [
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
        ];

        $response = $this->postJson('/api/premium/verify', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Premium status updated',
            ]);

        $this->assertDatabaseHas('premium_payments', [
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'status' => 'success',
        ]);
    }

    public function test_premium_verify_updates_existing_payment(): void
    {
        PremiumPayment::create([
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'pending',
            'platform' => 'android',
        ]);

        $payload = [
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
        ];

        $response = $this->postJson('/api/premium/verify', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('premium_payments', [
            'payment_id' => 'pay_123456',
            'status' => 'success',
        ]);
    }

    public function test_premium_verify_validation_fails(): void
    {
        $payload = [
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
        ];

        $response = $this->postJson('/api/premium/verify', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Payment validation failed',
            ]);
    }

    public function test_premium_status_returns_premium_user(): void
    {
        PremiumPayment::create([
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
            'verified_at' => now(),
        ]);

        $response = $this->getJson('/api/premium/status/test-anon-123');

        $response->assertStatus(200)
            ->assertJson([
                'anon_id' => 'test-anon-123',
                'is_premium' => true,
                'platform' => 'android',
            ])
            ->assertJsonStructure([
                'anon_id',
                'is_premium',
                'verified_at',
                'platform',
            ]);
    }

    public function test_premium_status_returns_non_premium_user(): void
    {
        $response = $this->getJson('/api/premium/status/test-anon-123');

        $response->assertStatus(200)
            ->assertJson([
                'anon_id' => 'test-anon-123',
                'is_premium' => false,
                'verified_at' => null,
                'platform' => null,
            ]);
    }

    public function test_rules_update_returns_empty_when_no_rules(): void
    {
        $response = $this->getJson('/api/rules/update');

        $response->assertStatus(200)
            ->assertJson([
                'version' => '1.0',
                'rules' => [],
            ]);
    }

    public function test_rules_update_returns_rules(): void
    {
        Rule::create([
            'version' => '1.0',
            'category' => 'Transport',
            'min_percent' => 10,
            'max_percent' => 25,
            'alert_threshold' => 0.2,
        ]);

        Rule::create([
            'version' => '1.0',
            'category' => 'Food',
            'min_percent' => 20,
            'max_percent' => 30,
            'alert_threshold' => 0.2,
        ]);

        $response = $this->getJson('/api/rules/update');

        $response->assertStatus(200)
            ->assertJson([
                'version' => '1.0',
                'rules' => [
                    [
                        'category' => 'Transport',
                        'min_percent' => 10,
                        'max_percent' => 25,
                        'alert_threshold' => 0.2,
                    ],
                    [
                        'category' => 'Food',
                        'min_percent' => 20,
                        'max_percent' => 30,
                        'alert_threshold' => 0.2,
                    ],
                ],
            ]);
    }

    public function test_premium_revoke_successfully_revokes_active_premium(): void
    {
        PremiumPayment::create([
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
            'verified_at' => now(),
        ]);

        $payload = [
            'anon_id' => 'test-anon-123',
            'reason' => 'Refund requested',
        ];

        $response = $this->postJson('/api/premium/revoke', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Premium access revoked',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'revoked_at',
            ]);

        $this->assertDatabaseHas('premium_payments', [
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
        ]);

        $payment = PremiumPayment::where('payment_id', 'pay_123456')->first();
        $this->assertNotNull($payment->revoked_at);
        $this->assertEquals('Refund requested', $payment->revoked_reason);
    }

    public function test_premium_revoke_returns_404_when_no_active_premium(): void
    {
        $payload = [
            'anon_id' => 'test-anon-123',
            'reason' => 'Test',
        ];

        $response = $this->postJson('/api/premium/revoke', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'No active premium subscription found',
            ]);
    }

    public function test_premium_revoke_validation_fails_without_anon_id(): void
    {
        $payload = [
            'reason' => 'Test',
        ];

        $response = $this->postJson('/api/premium/revoke', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ]);
    }

    public function test_premium_status_returns_false_for_revoked_premium(): void
    {
        PremiumPayment::create([
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
            'verified_at' => now(),
            'revoked_at' => now(),
            'revoked_reason' => 'Refund',
        ]);

        $response = $this->getJson('/api/premium/status/test-anon-123');

        $response->assertStatus(200)
            ->assertJson([
                'anon_id' => 'test-anon-123',
                'is_premium' => false,
                'verified_at' => null,
                'platform' => null,
            ]);
    }

    public function test_premium_revoke_uses_default_reason_when_not_provided(): void
    {
        PremiumPayment::create([
            'anon_id' => 'test-anon-123',
            'payment_id' => 'pay_123456',
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => 'success',
            'platform' => 'android',
            'verified_at' => now(),
        ]);

        $payload = [
            'anon_id' => 'test-anon-123',
        ];

        $response = $this->postJson('/api/premium/revoke', $payload);

        $response->assertStatus(200);

        $payment = PremiumPayment::where('payment_id', 'pay_123456')->first();
        $this->assertEquals('Manual revocation', $payment->revoked_reason);
    }
}
