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
            if (!Schema::hasColumn('interns', 'plain_password')) {
                $table->string('plain_password')->nullable()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            if (Schema::hasColumn('interns', 'plain_password')) {
                $table->dropColumn('plain_password');
            }
        });
    }
};
