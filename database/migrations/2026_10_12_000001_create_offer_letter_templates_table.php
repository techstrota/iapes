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
        if (!Schema::hasTable('offer_letter_templates')) {
            Schema::create('offer_letter_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('template')->default('3_month_offer_letter');
                $table->string('internship_role')->nullable();
                $table->string('internship_position')->nullable();
                $table->date('joining_date')->nullable();
                $table->date('completion_date')->nullable();
                $table->string('working_hours')->nullable()->default('11:00 AM to 4:00 PM, Monday to Saturday');
                $table->date('offer_issue_date')->nullable();
                $table->longText('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('offer_letters') && !Schema::hasColumn('offer_letters', 'offer_letter_template_id')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->foreignId('offer_letter_template_id')
                    ->nullable()
                    ->after('template')
                    ->constrained('offer_letter_templates')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('offer_letters') && Schema::hasColumn('offer_letters', 'offer_letter_template_id')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->dropForeign(['offer_letter_template_id']);
                $table->dropColumn('offer_letter_template_id');
            });
        }

        Schema::dropIfExists('offer_letter_templates');
    }
};
