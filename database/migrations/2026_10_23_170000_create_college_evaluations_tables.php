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
        Schema::create('college_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('college_name');
            $table->string('university_name')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('course_degree')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->date('evaluation_period_start')->nullable();
            $table->date('evaluation_period_end')->nullable();
            $table->decimal('total_marks', 8, 2)->default(100);
            $table->decimal('max_attendance_marks', 8, 2)->default(20);
            $table->decimal('max_task_marks', 8, 2)->default(40);
            $table->string('third_component_name')->nullable();
            $table->decimal('max_third_component_marks', 8, 2)->nullable();
            $table->text('instruction_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('college_name');
        });

        Schema::create('college_evaluation_interns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_evaluation_id')->constrained('college_evaluations')->cascadeOnDelete();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->decimal('attendance_pct', 5, 2)->nullable();
            $table->decimal('attendance_marks', 8, 2)->default(0);
            $table->decimal('task_completion_pct', 5, 2)->nullable();
            $table->decimal('task_marks', 8, 2)->default(0);
            $table->decimal('third_component_pct', 5, 2)->nullable();
            $table->decimal('third_component_marks', 8, 2)->nullable();
            $table->decimal('total_obtained_marks', 8, 2)->default(0);
            $table->string('grade', 20)->default('Pass');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['college_evaluation_id', 'intern_id'], 'col_eval_intern_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_evaluation_interns');
        Schema::dropIfExists('college_evaluations');
    }
};
