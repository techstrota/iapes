<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->string('offer_status')->default('draft')->after('is_accepted');
            // draft | accepted | rejected
        });

        // Migrate existing boolean data to the new column
        DB::table('offer_letters')->where('is_accepted', true)->update(['offer_status' => 'accepted']);
        DB::table('offer_letters')->where('is_accepted', false)->update(['offer_status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn('offer_status');
        });
    }
};
