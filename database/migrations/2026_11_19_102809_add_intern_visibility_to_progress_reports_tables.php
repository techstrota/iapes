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
            if (!Schema::hasColumn('progress_reports', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
                $table->index('is_published');
            }
        });

        Schema::table('progress_report_interns', function (Blueprint $table) {
            if (!Schema::hasColumn('progress_report_interns', 'is_visible_to_intern')) {
                $table->boolean('is_visible_to_intern')->default(false)->after('is_evaluated');
                $table->dateTime('shared_at')->nullable()->after('is_visible_to_intern');
                $table->index('is_visible_to_intern');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            if (Schema::hasColumn('progress_reports', 'is_published')) {
                $table->dropIndex(['is_published']);
                $table->dropColumn('is_published');
            }
        });

        Schema::table('progress_report_interns', function (Blueprint $table) {
            if (Schema::hasColumn('progress_report_interns', 'is_visible_to_intern')) {
                $table->dropIndex(['is_visible_to_intern']);
                $table->dropColumn(['is_visible_to_intern', 'shared_at']);
            }
        });
    }
};
