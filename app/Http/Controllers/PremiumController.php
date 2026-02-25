<?php

namespace App\Http\Controllers;

use App\Models\PremiumPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PremiumController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anon_id' => 'required|string',
            'payment_id' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'status' => 'required|in:pending,success,failed',
            'platform' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $existingPayment = PremiumPayment::where('payment_id', $request->payment_id)->first();

        if ($existingPayment) {
            $existingPayment->update([
                'status' => $request->status,
                'verified_at' => $request->status === 'success' ? now() : null,
            ]);
        } else {
            PremiumPayment::create([
                'anon_id' => $request->anon_id,
                'payment_id' => $request->payment_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => $request->status,
                'platform' => $request->platform,
                'verified_at' => $request->status === 'success' ? now() : null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Premium status updated',
        ], 200);
    }

    public function status(string $anon_id): JsonResponse
    {
        $payment = PremiumPayment::where('anon_id', $anon_id)
            ->where('status', 'success')
            ->whereNull('revoked_at')
            ->latest('verified_at')
            ->first();

        if (!$payment) {
            return response()->json([
                'anon_id' => $anon_id,
                'is_premium' => false,
                'verified_at' => null,
                'platform' => null,
            ], 200);
        }

        return response()->json([
            'anon_id' => $payment->anon_id,
            'is_premium' => true,
            'verified_at' => $payment->verified_at->toIso8601String(),
            'platform' => $payment->platform,
        ], 200);
    }

    public function revoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anon_id' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = PremiumPayment::where('anon_id', $request->anon_id)
            ->where('status', 'success')
            ->whereNull('revoked_at')
            ->latest('verified_at')
            ->first();

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active premium subscription found',
            ], 404);
        }

        $payment->update([
            'revoked_at' => now(),
            'revoked_reason' => $request->reason ?? 'Manual revocation',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Premium access revoked',
            'revoked_at' => $payment->revoked_at->toIso8601String(),
        ], 200);
    }
}
