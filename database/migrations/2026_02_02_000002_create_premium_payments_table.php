<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('anon_id')->index();
            $table->string('payment_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('platform');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_payments');
    }
};
