<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('current_plan_id')
                  ->nullable()
                  ->after('remember_token')
                  ->constrained('plans')
                  ->nullOnDelete();

            $table->integer('tasks_used_this_month')
                  ->default(0)
                  ->after('current_plan_id');

            $table->timestamp('task_cycle_start')
                  ->nullable()
                  ->after('tasks_used_this_month');

            $table->timestamp('task_cycle_end')
                  ->nullable()
                  ->after('task_cycle_start');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_plan_id']);
            $table->dropColumn([
                'current_plan_id',
                'tasks_used_this_month',
                'task_cycle_start',
                'task_cycle_end',
            ]);
        });
    }
};