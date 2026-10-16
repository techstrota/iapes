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
        Schema::create('daily_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->date('date');
            $table->string('title')->nullable();
            $table->longText('tasks_completed');
            $table->text('learnings')->nullable();
            $table->text('challenges')->nullable();
            $table->decimal('hours_worked', 4, 1)->default(8.0);
            $table->string('status', 50)->default('submitted'); // submitted, reviewed, approved
            $table->text('supervisor_remarks')->nullable();
            $table->timestamps();

            // Ensure only one log entry per intern per calendar date
            $table->unique(['intern_id', 'date']);
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logbooks');
    }
};
