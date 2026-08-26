<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The user's editable instance tree — one row per node at every depth level.
     * The pricing engine reads this table exclusively to compute all totals.
     *
     * Pricing engine rules:
     *  - Skip nodes where is_selected = false and all their descendants
     *  - Only charge leaf nodes (nodes with no children in this tree)
     *  - depth=0 root: if expert_fee_mode = AUTOMATION_LEVEL, charge automation_expert_fee once
     *  - selected_dimensions JSON + providers.multipliers JSON = final dimension multiplier
     */
    public function up(): void
    {
        Schema::create('quotation_nodes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // ── Tree structure ───────────────────────────────────────────────
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_node_id')->nullable(); // self-ref; NULL = root
            $table->foreignId('source_component_id')
                  ->nullable()
                  ->constrained('components')
                  ->nullOnDelete();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('depth')->default(0); // 0 = automation root
            $table->boolean('is_custom')->default(false);     // true = not from library
            $table->boolean('is_selected')->default(true);    // false = excluded from pricing

            // ── Pricing ──────────────────────────────────────────────────────
            $table->foreignId('pricing_category_id')
                  ->nullable()
                  ->constrained('pricing_categories')
                  ->nullOnDelete();

            $table->enum('pricing_method', [
                'fixed', 'qty_unit', 'percentage',
                'formula', 'usage_estimation', 'manual',
            ])->nullable();

            $table->enum('billing_type', ['ONE_TIME', 'RECURRING'])->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 10, 4)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();  // overrides provider rate if set
            $table->text('formula')->nullable();

            // ── Provider selection ───────────────────────────────────────────
            $table->foreignId('selected_provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // {"resolution": "1080p", "quality": "standard"} → looked up in providers.multipliers
            $table->json('selected_dimensions')->nullable();

            // For unknown providers in custom automations
            $table->string('custom_provider_name', 200)->nullable();
            $table->enum('feasibility_status', [
                'not_required', 'pending', 'approved', 'rejected',
            ])->default('not_required');

            // ── Expert fee (root nodes only) ─────────────────────────────────
            $table->enum('expert_fee_mode', [
                'COMPONENT_LEVEL', 'AUTOMATION_LEVEL',
            ])->nullable();
            $table->decimal('automation_expert_fee', 10, 2)->nullable();

            // ── Admin ────────────────────────────────────────────────────────
            $table->decimal('internal_cost', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // ── Self-referencing FK ──────────────────────────────────────────
            $table->foreign('parent_node_id')
                  ->references('id')->on('quotation_nodes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_nodes');
    }
};
