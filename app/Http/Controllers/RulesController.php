<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\JsonResponse;

class RulesController extends Controller
{
    public function update(): JsonResponse
    {
        $rules = Rule::all();

        if ($rules->isEmpty()) {
            return response()->json([
                'version' => '1.0',
                'rules' => [],
            ], 200);
        }

        $version = $rules->first()->version ?? '1.0';

        $formattedRules = $rules->map(function ($rule) {
            return [
                'category' => $rule->category,
                'min_percent' => $rule->min_percent,
                'max_percent' => $rule->max_percent,
                'alert_threshold' => (float) $rule->alert_threshold,
            ];
        });

        return response()->json([
            'version' => $version,
            'rules' => $formattedRules,
        ], 200);
    }
}
