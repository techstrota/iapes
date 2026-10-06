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
        Schema::table('intern_teams', function (Blueprint $table) {
            $table->text('project_description')->nullable()->after('team_name');
            $table->string('track')->nullable()->default('Engineering')->after('project_description');
            $table->string('status')->nullable()->default('on_track')->after('track'); // on_track, attention, completed
            $table->string('mentor_name')->nullable()->after('status');
            $table->string('mentor_title')->nullable()->after('mentor_name');
            $table->string('mentor_avatar')->nullable()->after('mentor_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_teams', function (Blueprint $table) {
            $table->dropColumn([
                'project_description',
                'track',
                'status',
                'mentor_name',
                'mentor_title',
                'mentor_avatar',
            ]);
        });
    }
};
