<?php

namespace App\Http\Controllers;

use App\Models\Snapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SnapshotController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anon_id' => 'required|string',
            'encrypted_blob' => 'required|string',
            'schema_version' => 'required|string',
            'checksum' => 'required|string',
            'last_sync' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingSnapshot = Snapshot::where('anon_id', $request->anon_id)->first();

        if ($existingSnapshot) {
            $existingSnapshot->update($request->only([
                'encrypted_blob',
                'schema_version',
                'checksum',
                'last_sync',
            ]));
        } else {
            Snapshot::create($request->only([
                'anon_id',
                'encrypted_blob',
                'schema_version',
                'checksum',
                'last_sync',
            ]));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Snapshot uploaded successfully',
        ], 200);
    }

    public function fetch(string $anon_id): JsonResponse
    {
        $snapshot = Snapshot::where('anon_id', $anon_id)->first();

        if (!$snapshot) {
            return response()->json([
                'status' => 'error',
                'message' => 'Snapshot not found',
            ], 404);
        }

        return response()->json([
            'anon_id' => $snapshot->anon_id,
            'encrypted_blob' => $snapshot->encrypted_blob,
            'schema_version' => $snapshot->schema_version,
            'checksum' => $snapshot->checksum,
            'last_sync' => $snapshot->last_sync->toIso8601String(),
        ], 200);
    }
}
