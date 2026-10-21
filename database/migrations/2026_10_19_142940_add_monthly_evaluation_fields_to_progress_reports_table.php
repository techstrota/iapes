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
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->string('report_type')->default('weekly')->after('title');
            $table->json('evaluation_criteria')->nullable()->after('team_id');
        });

        Schema::table('progress_report_interns', function (Blueprint $table) {
            $table->json('criteria_ratings')->nullable()->after('supervisor_remarks');
            $table->text('mentor_final_comments')->nullable()->after('criteria_ratings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropColumn(['report_type', 'evaluation_criteria']);
        });

        Schema::table('progress_report_interns', function (Blueprint $table) {
            $table->dropColumn(['criteria_ratings', 'mentor_final_comments']);
        });
    }
};
