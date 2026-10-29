<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->string('evaluation_mode', 20)->default('criteria')->after('duration');
            $table->json('columns_config')->nullable()->after('custom_criteria');
        });

        Schema::table('college_evaluation_interns', function (Blueprint $table) {
            $table->unsignedBigInteger('intern_id')->nullable()->change();
            $table->string('custom_student_id', 100)->nullable()->after('intern_id');
            $table->string('custom_student_name', 255)->nullable()->after('custom_student_id');
            $table->json('row_data')->nullable()->after('marks');
            $table->integer('sort_order')->default(0)->after('remarks');
        });

        // Drop the strict unique index if it exists, to allow manual rows or multiple entries cleanly
        try {
            Schema::table('college_evaluation_interns', function (Blueprint $table) {
                $table->dropUnique('col_eval_intern_unique');
            });
        } catch (\Throwable $e) {
            // Index might not exist or already dropped
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_evaluation_interns', function (Blueprint $table) {
            $table->dropColumn(['custom_student_id', 'custom_student_name', 'row_data', 'sort_order']);
        });

        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->dropColumn(['evaluation_mode', 'columns_config']);
        });
    }
};

