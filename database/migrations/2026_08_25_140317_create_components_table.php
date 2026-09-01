<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('parent_id')->nullable();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('is_bundle')->default(false);
            $table->boolean('is_leaf')->default(false);
            $table->string('platform', 100)->nullable();
            $table->string('category', 100)->nullable();

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
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('quantity', 10, 4)->nullable();
            $table->text('formula')->nullable();
            $table->decimal('internal_cost', 10, 2)->nullable();

            $table->enum('expert_fee_mode', [
                'COMPONENT_LEVEL', 'AUTOMATION_LEVEL',
            ])->nullable();
            $table->decimal('automation_expert_fee', 10, 2)->default(0);

            $table->json('available_providers')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('components', function (Blueprint $table) {
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};