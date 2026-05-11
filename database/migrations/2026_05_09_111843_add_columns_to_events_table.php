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
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('event_date', 'event_start_date');
             $table->renameColumn('location', 'event_venue');
            $table->date('event_end_date')->nullable()->after('event_start_date');
            $table->text('meeting_platform')->nullable()->after('meeting_link');
            $table->text('skills')->nullable()->after('event_status');
             if (Schema::hasColumn('events', 'event_certificate_template')) {
                $table->dropColumn('event_certificate_template');
            }
            $table->string('event_type')->change();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['skills','event_end_date','meeting_platform']);
            $table->renameColumn('event_start_date', 'event_date');
             $table->renameColumn('event_venue', 'location');
        });
    }
};
