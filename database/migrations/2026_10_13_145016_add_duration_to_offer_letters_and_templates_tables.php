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
        if (Schema::hasTable('offer_letters') && !Schema::hasColumn('offer_letters', 'duration')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->string('duration')->nullable()->after('completion_date');
            });
        }

        if (Schema::hasTable('offer_letter_templates') && !Schema::hasColumn('offer_letter_templates', 'duration')) {
            Schema::table('offer_letter_templates', function (Blueprint $table) {
                $table->string('duration')->nullable()->after('completion_date');
            });
        }

        // Backfill default durations for existing records
        if (Schema::hasTable('offer_letters')) {
            DB::table('offer_letters')->whereNull('duration')->orWhere('duration', '')->update([
                'duration' => DB::raw("CASE 
                    WHEN template = 'one_month' THEN '1 month'
                    WHEN template = '3_month_offer_letter' THEN '3 months'
                    WHEN template = '4_month_offer_letter' THEN '4 months'
                    WHEN template = '6_month_offer_letter' THEN '6 months'
                    ELSE '3 months'
                END")
            ]);

            // Migrate legacy template column values to the 2 core templates
            DB::table('offer_letters')->whereIn('template', ['one_month', '3_month_offer_letter', 'general'])->update([
                'template' => 'single_page'
            ]);
            DB::table('offer_letters')->whereIn('template', ['4_month_offer_letter', '6_month_offer_letter'])->update([
                'template' => 'multi_page'
            ]);
        }

        if (Schema::hasTable('offer_letter_templates')) {
            DB::table('offer_letter_templates')->whereNull('duration')->orWhere('duration', '')->update([
                'duration' => DB::raw("CASE 
                    WHEN template = 'one_month' THEN '1 month'
                    WHEN template = '3_month_offer_letter' THEN '3 months'
                    WHEN template = '4_month_offer_letter' THEN '4 months'
                    WHEN template = '6_month_offer_letter' THEN '6 months'
                    ELSE '3 months'
                END")
            ]);

            DB::table('offer_letter_templates')->whereIn('template', ['one_month', '3_month_offer_letter', 'general'])->update([
                'template' => 'single_page'
            ]);
            DB::table('offer_letter_templates')->whereIn('template', ['4_month_offer_letter', '6_month_offer_letter'])->update([
                'template' => 'multi_page'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('offer_letters') && Schema::hasColumn('offer_letters', 'duration')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->dropColumn('duration');
            });
        }

        if (Schema::hasTable('offer_letter_templates') && Schema::hasColumn('offer_letter_templates', 'duration')) {
            Schema::table('offer_letter_templates', function (Blueprint $table) {
                $table->dropColumn('duration');
            });
        }
    }
};
