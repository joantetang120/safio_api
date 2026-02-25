<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'version' => '1.0',
                'category' => 'Transport',
                'min_percent' => 10,
                'max_percent' => 25,
                'alert_threshold' => 0.2,
            ],
            [
                'version' => '1.0',
                'category' => 'Food',
                'min_percent' => 20,
                'max_percent' => 30,
                'alert_threshold' => 0.2,
            ],
            [
                'version' => '1.0',
                'category' => 'Housing',
                'min_percent' => 25,
                'max_percent' => 35,
                'alert_threshold' => 0.15,
            ],
            [
                'version' => '1.0',
                'category' => 'Entertainment',
                'min_percent' => 5,
                'max_percent' => 15,
                'alert_threshold' => 0.25,
            ],
            [
                'version' => '1.0',
                'category' => 'Healthcare',
                'min_percent' => 5,
                'max_percent' => 15,
                'alert_threshold' => 0.2,
            ],
            [
                'version' => '1.0',
                'category' => 'Savings',
                'min_percent' => 10,
                'max_percent' => 30,
                'alert_threshold' => 0.15,
            ],
        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }
    }
}
