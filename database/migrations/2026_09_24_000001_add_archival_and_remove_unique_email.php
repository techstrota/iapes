<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove unique constraint on email in interns table
        if (Schema::hasTable('interns') && Schema::hasColumn('interns', 'email')) {
            $internIndexes = collect(DB::select("SHOW INDEXES FROM interns WHERE Column_name = 'email'"));
            $uniqueIndex = $internIndexes->firstWhere('Non_unique', 0);
            if ($uniqueIndex) {
                $indexName = $uniqueIndex->Key_name;
                Schema::table('interns', function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            }
        }

        // 2. Remove unique constraint on email in applications table (if any exists)
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'email')) {
            $appIndexes = collect(DB::select("SHOW INDEXES FROM applications WHERE Column_name = 'email'"));
            $appUniqueIndex = $appIndexes->firstWhere('Non_unique', 0);
            if ($appUniqueIndex) {
                $indexName = $appUniqueIndex->Key_name;
                Schema::table('applications', function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            }
        }

        // 3. Add archival columns to applications table
        if (Schema::hasTable('applications')) {
            Schema::table('applications', function (Blueprint $table) {
                if (!Schema::hasColumn('applications', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->index()->after('status');
                }
                if (!Schema::hasColumn('applications', 'cohort_archive_name')) {
                    $table->string('cohort_archive_name')->nullable()->index()->after('is_archived');
                }
                if (!Schema::hasColumn('applications', 'archive_note')) {
                    $table->text('archive_note')->nullable()->after('cohort_archive_name');
                }
                if (!Schema::hasColumn('applications', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('archive_note');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('applications')) {
            Schema::table('applications', function (Blueprint $table) {
                if (Schema::hasColumn('applications', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
                if (Schema::hasColumn('applications', 'archive_note')) {
                    $table->dropColumn('archive_note');
                }
                if (Schema::hasColumn('applications', 'cohort_archive_name')) {
                    $table->dropColumn('cohort_archive_name');
                }
                if (Schema::hasColumn('applications', 'is_archived')) {
                    $table->dropColumn('is_archived');
                }
            });
        }
    }
};
