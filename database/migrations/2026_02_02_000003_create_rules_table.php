<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version');
            $table->string('category');
            $table->integer('min_percent');
            $table->integer('max_percent');
            $table->decimal('alert_threshold', 3, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
