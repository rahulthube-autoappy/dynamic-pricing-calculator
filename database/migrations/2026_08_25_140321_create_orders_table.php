<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('quotation_id')->constrained('quotations');
            $table->foreignUuid('plan_id')->constrained('plans');

            $table->string('idempotency_key', 100)->unique();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->char('currency', 3)->default('INR');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('expert_fee_total', 12, 2);
            $table->decimal('one_time_total', 12, 2);
            $table->decimal('recurring_monthly_total', 12, 2);
            $table->decimal('plan_price', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};