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
        Schema::table('interns', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('college')->nullable()->after('phone');
            $table->string('degree')->nullable()->after('college');
            $table->string('university')->nullable()->after('degree');
            $table->string('academic_year')->nullable()->after('university');
            $table->decimal('cgpa', 5, 2)->nullable()->after('academic_year');
            $table->string('domain')->nullable()->after('cgpa');
            $table->text('skills')->nullable()->after('domain');
            $table->date('joining_date')->nullable()->after('skills');
            $table->date('completion_date')->nullable()->after('joining_date');
            $table->string('internship_role')->nullable()->after('completion_date');
            $table->string('internship_position')->nullable()->after('internship_role');
            $table->string('working_hours')->nullable()->default('42 hours per week')->after('internship_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'college',
                'degree',
                'university',
                'academic_year',
                'cgpa',
                'domain',
                'skills',
                'joining_date',
                'completion_date',
                'internship_role',
                'internship_position',
                'working_hours',
            ]);
        });
    }
};
