<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Covers both the active cart and saved custom automations via the type column.
     * type='cart'             → user's active pricing builder (one draft per user)
     * type='custom_automation'→ user's saved bespoke automation (many per user allowed)
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('type', ['cart', 'custom_automation'])->default('cart');
            $table->string('title', 200)->nullable();          // required for custom_automation

            $table->foreignId('source_component_id')           // which bundle was copied
                  ->nullable()
                  ->constrained('components')
                  ->nullOnDelete();

            $table->foreignId('selected_plan_id')
                  ->nullable()
                  ->constrained('plans')
                  ->nullOnDelete();

            $table->boolean('requires_expert')->default(false); // always true for custom_automation
            $table->text('expert_notes')->nullable();

            $table->enum('status', [
                'draft', 'active', 'submitted', 'checked_out', 'archived',
            ])->default('draft');

            $table->string('idempotency_key', 100)->nullable()->unique(); // prevent duplicate checkout
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
