<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PremiumPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'anon_id',
        'payment_id',
        'amount',
        'currency',
        'status',
        'platform',
        'verified_at',
        'revoked_at',
        'revoked_reason',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
        'amount' => 'decimal:2',
    ];
}
