<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Finalised quotation after checkout. All monetary amounts are locked at this point.
     * idempotency_key prevents double-orders on double-click.
     * tax_total column exists; GST calculation logic is added during application build.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained('quotations');
            $table->foreignId('plan_id')->constrained('plans');

            $table->string('idempotency_key', 100)->unique(); // prevents double-order
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->char('currency', 3)->default('INR');

            // ── Price breakdown ──────────────────────────────────────────────
            $table->decimal('subtotal', 12, 2);               // sum of all billable nodes
            $table->decimal('expert_fee_total', 12, 2);       // subset of subtotal
            $table->decimal('one_time_total', 12, 2);         // subset of subtotal
            $table->decimal('recurring_monthly_total', 12, 2);// subset of subtotal
            $table->decimal('plan_price', 12, 2);             // plan price at checkout
            $table->decimal('discount_total', 12, 2)->default(0); // future use
            $table->decimal('tax_total', 12, 2)->default(0);  // GST — logic added during build
            $table->decimal('grand_total', 12, 2);            // subtotal + plan_price - discount + tax

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
