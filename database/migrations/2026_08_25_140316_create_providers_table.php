<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 200);
            $table->string('code', 100)->unique();
            $table->string('provider_company', 100);
            $table->string('provider_company_code', 100);
            $table->text('description')->nullable();
            $table->json('capabilities')->nullable();
            $table->string('billing_unit', 50)->nullable();
            $table->integer('billing_granularity')->default(1);
            $table->boolean('allow_decimals')->default(false);
            $table->decimal('input_rate', 10, 6)->nullable();
            $table->decimal('output_rate', 10, 6)->nullable();
            $table->decimal('rate', 10, 6)->nullable();
            $table->json('multipliers')->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};