<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('start_time', 20)->nullable()->after('status');
            $table->string('end_time', 20)->nullable()->after('start_time');
            $table->unsignedInteger('late_minutes')->nullable()->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'late_minutes']);
        });
    }
};
