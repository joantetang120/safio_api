<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'anon_id',
        'encrypted_blob',
        'schema_version',
        'checksum',
        'last_sync',
    ];

    protected $casts = [
        'last_sync' => 'datetime',
    ];
}
