<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Immutable historical record — INSERT ONLY, never updated.
     * Preserves the full pricing tree at the moment of checkout.
     * Changing a provider's rate tomorrow will NOT affect existing snapshot rows.
     *
     * All price-affecting data is snapshotted as plain strings/decimals (no FKs to
     * providers or pricing_categories) — so renaming/deleting a provider never
     * corrupts historical records.
     *
     * Rows at depth = 0 serve as per-automation summary rows, replacing the need
     * for a separate order_automations table.
     */
    public function up(): void
    {
        Schema::create('pricing_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('quotation_node_id')->constrained('quotation_nodes');
            $table->unsignedBigInteger('parent_snapshot_id')->nullable(); // self-ref tree

            // ── Tree position ────────────────────────────────────────────────
            $table->unsignedTinyInteger('depth');  // 0 = automation summary row

            // ── Snapshotted data (plain values, no FKs) ──────────────────────
            $table->string('node_name', 200);
            $table->string('pricing_category', 100)->nullable();  // plain string copy
            $table->string('pricing_method', 50)->nullable();
            $table->enum('billing_type', ['ONE_TIME', 'RECURRING'])->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 10, 4)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();     // locked at checkout
            $table->decimal('calculated_total', 12, 2);
            $table->string('provider_name', 200)->nullable();     // "GPT-4o (OpenAI)"
            $table->json('selected_dimensions')->nullable();       // dimension choices snapshot

            $table->timestamp('created_at')->useCurrent();

            // ── Self-referencing FK ──────────────────────────────────────────
            $table->foreign('parent_snapshot_id')
                  ->references('id')->on('pricing_snapshots')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_snapshots');
    }
};
