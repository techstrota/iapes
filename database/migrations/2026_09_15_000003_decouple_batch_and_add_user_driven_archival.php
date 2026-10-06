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
        // 1. Decouple intern_teams from internship_batches (make nullable) and add archival fields
        if (Schema::hasTable('intern_teams')) {
            // Raw SQL to safely ensure nullable on MySQL without requiring doctrine/dbal
            try {
                DB::statement('ALTER TABLE `intern_teams` MODIFY `internship_batch_id` BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                Schema::table('intern_teams', function (Blueprint $table) {
                    if (Schema::hasColumn('intern_teams', 'internship_batch_id')) {
                        $table->unsignedBigInteger('internship_batch_id')->nullable()->change();
                    }
                });
            }

            Schema::table('intern_teams', function (Blueprint $table) {
                if (!Schema::hasColumn('intern_teams', 'cohort_archive_name')) {
                    $table->string('cohort_archive_name')->nullable()->after('status');
                }
                if (!Schema::hasColumn('intern_teams', 'archive_note')) {
                    $table->text('archive_note')->nullable()->after('cohort_archive_name');
                }
                if (!Schema::hasColumn('intern_teams', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->after('archive_note');
                }
            });
        }

        // 2. Add archival fields to interns table
        if (Schema::hasTable('interns')) {
            Schema::table('interns', function (Blueprint $table) {
                if (!Schema::hasColumn('interns', 'cohort_archive_name')) {
                    $table->string('cohort_archive_name')->nullable()->after('academic_year');
                }
                if (!Schema::hasColumn('interns', 'archive_note')) {
                    $table->text('archive_note')->nullable()->after('cohort_archive_name');
                }
                if (!Schema::hasColumn('interns', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('archive_note');
                }
            });
        }

        // 3. Add archival fields to internship_batches table
        if (Schema::hasTable('internship_batches')) {
            Schema::table('internship_batches', function (Blueprint $table) {
                if (!Schema::hasColumn('internship_batches', 'cohort_archive_name')) {
                    $table->string('cohort_archive_name')->nullable()->after('no_of_interns');
                }
                if (!Schema::hasColumn('internship_batches', 'status')) {
                    $table->string('status')->default('active')->after('cohort_archive_name');
                }
                if (!Schema::hasColumn('internship_batches', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('intern_teams')) {
            Schema::table('intern_teams', function (Blueprint $table) {
                $table->dropColumn(['cohort_archive_name', 'archive_note', 'is_archived']);
            });
        }

        if (Schema::hasTable('interns')) {
            Schema::table('interns', function (Blueprint $table) {
                $table->dropColumn(['cohort_archive_name', 'archive_note', 'archived_at']);
            });
        }

        if (Schema::hasTable('internship_batches')) {
            Schema::table('internship_batches', function (Blueprint $table) {
                $table->dropColumn(['cohort_archive_name', 'status', 'is_archived']);
            });
        }
    }
};
