<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['cart', 'custom_automation'])->default('cart');
            $table->string('title', 200)->nullable();

            $table->foreignUuid('source_component_id')
                  ->nullable()
                  ->constrained('components')
                  ->nullOnDelete();

            $table->foreignUuid('selected_plan_id')
                  ->nullable()
                  ->constrained('plans')
                  ->nullOnDelete();

            $table->boolean('requires_expert')->default(false);
            $table->text('expert_notes')->nullable();

            $table->enum('status', [
                'draft', 'active', 'submitted', 'checked_out', 'archived',
            ])->default('draft');

            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};