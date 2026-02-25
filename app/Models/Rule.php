<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasUuids;

    protected $fillable = [
        'version',
        'category',
        'min_percent',
        'max_percent',
        'alert_threshold',
    ];

    protected $casts = [
        'min_percent' => 'integer',
        'max_percent' => 'integer',
        'alert_threshold' => 'decimal:2',
    ];
}
