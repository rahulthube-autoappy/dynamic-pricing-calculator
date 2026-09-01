<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('quotation_node_id')->constrained('quotation_nodes');
            $table->foreignUuid('parent_snapshot_id')->nullable()->constrained('pricing_snapshots')->cascadeOnDelete();

            $table->unsignedTinyInteger('depth');

            $table->string('node_name', 200);
            $table->string('pricing_category', 100)->nullable();
            $table->string('pricing_method', 50)->nullable();
            $table->enum('billing_type', ['ONE_TIME', 'RECURRING'])->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 10, 4)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('calculated_total', 12, 2);
            $table->string('provider_name', 200)->nullable();
            $table->json('selected_dimensions')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_snapshots');
    }
};