<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add custom_criteria (JSON) to college_evaluations
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->json('custom_criteria')->nullable()->after('max_task_marks');
        });

        // Migrate existing 3rd component to custom_criteria
        $evaluations = DB::table('college_evaluations')->get();
        foreach ($evaluations as $eval) {
            $custom = [];
            if (!empty($eval->third_component_name) && (float)($eval->max_third_component_marks ?? 0) > 0) {
                $custom[] = [
                    'name'      => $eval->third_component_name,
                    'max_marks' => (float)$eval->max_third_component_marks,
                ];
            }
            DB::table('college_evaluations')->where('id', $eval->id)->update([
                'custom_criteria' => !empty($custom) ? json_encode($custom) : null,
            ]);
        }

        // 2. Add marks (JSON) to college_evaluation_interns
        Schema::table('college_evaluation_interns', function (Blueprint $table) {
            $table->json('marks')->nullable()->after('intern_id');
        });

        // Migrate existing intern marks to JSON
        $internEvals = DB::table('college_evaluation_interns')->get();
        foreach ($internEvals as $ie) {
            $eval = DB::table('college_evaluations')->where('id', $ie->college_evaluation_id)->first();
            $attMarks = (float)($ie->attendance_marks ?? 0);
            $attPct   = $ie->attendance_pct !== null ? (float)$ie->attendance_pct : null;
            $taskMarks = (float)($ie->task_marks ?? 0);
            $taskPct   = $ie->task_completion_pct !== null ? (float)$ie->task_completion_pct : null;

            $customMarks = [];
            if (!empty($eval->third_component_name) && $ie->third_component_marks !== null) {
                $customMarks[] = [
                    'name'      => $eval->third_component_name,
                    'max_marks' => (float)($eval->max_third_component_marks ?? 0),
                    'marks'     => (float)$ie->third_component_marks,
                    'pct'       => $ie->third_component_pct !== null ? (float)$ie->third_component_pct : null,
                ];
            }

            $marksData = [
                'attendance_marks' => $attMarks,
                'attendance_pct'   => $attPct,
                'task_marks'       => $taskMarks,
                'task_pct'         => $taskPct,
                'custom'           => $customMarks,
            ];

            DB::table('college_evaluation_interns')->where('id', $ie->id)->update([
                'marks' => json_encode($marksData),
            ]);
        }

        // 3. Drop legacy columns from college_evaluations
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->dropColumn(['third_component_name', 'max_third_component_marks']);
        });

        // 4. Drop legacy columns from college_evaluation_interns
        Schema::table('college_evaluation_interns', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_pct',
                'attendance_marks',
                'task_completion_pct',
                'task_marks',
                'third_component_pct',
                'third_component_marks',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->string('third_component_name')->nullable()->after('max_task_marks');
            $table->decimal('max_third_component_marks', 8, 2)->nullable()->after('third_component_name');
            $table->dropColumn('custom_criteria');
        });

        Schema::table('college_evaluation_interns', function (Blueprint $table) {
            $table->decimal('attendance_pct', 5, 2)->nullable();
            $table->decimal('attendance_marks', 8, 2)->default(0);
            $table->decimal('task_completion_pct', 5, 2)->nullable();
            $table->decimal('task_marks', 8, 2)->default(0);
            $table->decimal('third_component_pct', 5, 2)->nullable();
            $table->decimal('third_component_marks', 8, 2)->nullable();
            $table->dropColumn('marks');
        });
    }
};
