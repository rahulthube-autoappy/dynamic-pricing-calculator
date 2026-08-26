<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Subscription plans: Starter (free), Growth (₹999), Professional (₹1,999).
     * max_tasks = AutoAppy workflow run limit per month. NULL = unlimited.
     * Third-party resource limits are governed by individual providers, not plans.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100)->unique();         // "starter", "growth", "professional"
            $table->decimal('price', 10, 2)->default(0);  // 0 for Starter
            $table->integer('max_tasks')->nullable();      // NULL = unlimited (Professional)
            $table->text('description')->nullable();
            $table->json('features')->nullable();          // UI feature list
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
