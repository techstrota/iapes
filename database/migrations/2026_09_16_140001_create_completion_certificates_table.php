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
        Schema::create('completion_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();

            // Reference & verification
            $table->string('cert_ref_id')->unique();
            $table->string('cert_token')->unique();

            // Intern identity snapshot
            $table->string('intern_name')->nullable();
            $table->string('intern_code')->nullable();
            $table->string('internship_role')->nullable();

            // Internship dates
            $table->date('joining_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('issuing_date')->nullable();

            // Evaluation
            $table->string('project_name')->nullable();
            $table->string('grade')->nullable();

            // Audit
            $table->string('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('completion_certificates');
    }
};
