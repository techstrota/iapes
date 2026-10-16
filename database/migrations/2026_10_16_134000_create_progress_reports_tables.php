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
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->string('selection_mode', 50)->default('intern'); // 'intern', 'batch', 'team'
            $table->foreignId('batch_id')->nullable()->constrained('internship_batches')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('intern_teams')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('in_progress'); // 'in_progress', 'completed'
            $table->timestamps();

            $table->index('week_start_date');
            $table->index('week_end_date');
        });

        Schema::create('progress_report_interns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_report_id')->constrained('progress_reports')->cascadeOnDelete();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->longText('weekly_summary')->nullable();
            $table->longText('supervisor_remarks')->nullable();
            $table->boolean('is_evaluated')->default(false);
            $table->dateTime('evaluated_at')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['progress_report_id', 'intern_id']);
            $table->index('is_evaluated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_report_interns');
        Schema::dropIfExists('progress_reports');
    }
};
