<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reusable master component library. Self-referencing tree (unlimited depth).
     * is_bundle = true  → top-level automation / bundle (root of a pricing tree)
     * is_leaf   = true  → billable leaf node (no children)
     * available_providers JSON references model-level providers.id values.
     * Master rows are NEVER modified when a user adds them to a quotation.
     */
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // ── Tree structure ───────────────────────────────────────────────
            $table->unsignedBigInteger('parent_id')->nullable();  // self-ref; NULL = root
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('is_bundle')->default(false);          // top-level automation
            $table->boolean('is_leaf')->default(false);            // billable, no children
            $table->string('platform', 100)->nullable();           // "YouTube", "Instagram"
            $table->string('category', 100)->nullable();

            // ── Pricing ──────────────────────────────────────────────────────
            $table->foreignId('pricing_category_id')
                  ->nullable()
                  ->constrained('pricing_categories')
                  ->nullOnDelete();

            $table->enum('pricing_method', [
                'fixed', 'qty_unit', 'percentage',
                'formula', 'usage_estimation', 'manual',
            ])->nullable();                                        // only when is_leaf = true

            $table->enum('billing_type', ['ONE_TIME', 'RECURRING'])->nullable();
            $table->string('unit', 50)->nullable();               // "video", "token", "second"
            $table->decimal('unit_price', 10, 2)->nullable();     // base price, no provider
            $table->decimal('quantity', 10, 4)->nullable();        // default quantity
            $table->text('formula')->nullable();
            $table->decimal('internal_cost', 10, 2)->nullable();  // admin-only

            // ── Expert fee (bundle nodes only) ───────────────────────────────
            $table->enum('expert_fee_mode', [
                'COMPONENT_LEVEL', 'AUTOMATION_LEVEL',
            ])->nullable();
            $table->decimal('automation_expert_fee', 10, 2)->default(0);

            // ── Provider options ─────────────────────────────────────────────
            // [{"provider_id": 4, "is_default": true}, {"provider_id": 5, "is_default": false}]
            $table->json('available_providers')->nullable();

            // ── Meta ─────────────────────────────────────────────────────────
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();  // FK to users added below
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // ── Self-referencing FK ──────────────────────────────────────────
            $table->foreign('parent_id')
                  ->references('id')->on('components')
                  ->onDelete('set null');
        });

        // created_by → users (added after table creation to avoid ordering issues)
        Schema::table('components', function (Blueprint $table) {
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
