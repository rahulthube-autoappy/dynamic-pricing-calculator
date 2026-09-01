<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignUuid('parent_node_id')->nullable()->constrained('quotation_nodes')->cascadeOnDelete();
            $table->foreignUuid('source_component_id')
                  ->nullable()
                  ->constrained('components')
                  ->nullOnDelete();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->boolean('is_custom')->default(false);
            $table->boolean('is_selected')->default(true);

            $table->foreignUuid('pricing_category_id')
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
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('formula')->nullable();

            $table->foreignUuid('selected_provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            $table->json('selected_dimensions')->nullable();
            $table->string('custom_provider_name', 200)->nullable();
            $table->enum('feasibility_status', [
                'not_required', 'pending', 'approved', 'rejected',
            ])->default('not_required');

            $table->enum('expert_fee_mode', [
                'COMPONENT_LEVEL', 'AUTOMATION_LEVEL',
            ])->nullable();
            $table->decimal('automation_expert_fee', 10, 2)->nullable();

            $table->decimal('internal_cost', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_nodes');
    }
};