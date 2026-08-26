<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Model-level rows — each row IS a specific AI model (e.g. GPT-4o, DALL-E 3, Gen-3 Alpha).
     * provider_company groups them (e.g. "OpenAI").
     * Meter data (billing_unit, rates, granularity) and dimension multipliers live here.
     * Text models use input_rate + output_rate. Media models use a single rate.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 100)->unique();             // "gpt-4o", "dall-e-3", "gen-3-alpha"
            $table->string('provider_company', 100);          // "OpenAI", "RunwayML"
            $table->string('provider_company_code', 100);     // "openai", "runwayml"
            $table->text('description')->nullable();
            $table->json('capabilities')->nullable();          // ["text_generation", "image_generation"]

            // ── Meter / billing ──────────────────────────────────────────────
            $table->string('billing_unit', 50)->nullable();   // "token", "image", "second", "character"
            $table->integer('billing_granularity')->default(1); // 1000 for tokens, 1 for images/seconds
            $table->boolean('allow_decimals')->default(false);

            // ── Rates ────────────────────────────────────────────────────────
            $table->decimal('input_rate', 10, 6)->nullable();  // text models: per billing_granularity input
            $table->decimal('output_rate', 10, 6)->nullable(); // text models: per billing_granularity output
            $table->decimal('rate', 10, 6)->nullable();        // media models: single rate per unit

            // ── Dimension multipliers ────────────────────────────────────────
            // e.g. {"resolution": {"720p": 0.8, "1080p": 1.0, "4K": 2.0},
            //        "quality":    {"draft": 0.8, "standard": 1.0, "ultra": 1.3}}
            $table->json('multipliers')->nullable();

            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
