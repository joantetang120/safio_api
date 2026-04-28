<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('premium_payments', function (Blueprint $table) {
            $table->string('geniuspay_reference')->nullable()->unique()->after('payment_id');
            $table->enum('plan', ['monthly', 'yearly', 'lifetime'])->nullable()->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('premium_payments', function (Blueprint $table) {
            $table->dropColumn(['geniuspay_reference', 'plan']);
        });
    }
};
