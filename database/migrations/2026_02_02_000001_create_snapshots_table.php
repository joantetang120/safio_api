<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('anon_id')->index();
            $table->longText('encrypted_blob');
            $table->string('schema_version');
            $table->string('checksum');
            $table->timestamp('last_sync');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
