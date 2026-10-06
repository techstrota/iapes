<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `attendances` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'unmarked'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `attendances` MODIFY `status` ENUM('present', 'absent', 'late', 'leave') NOT NULL DEFAULT 'present'");
    }
};
