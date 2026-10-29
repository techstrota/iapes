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
        // 1. Add address column if it doesn't already exist
        if (!Schema::hasColumn('colleges', 'address')) {
            Schema::table('colleges', function (Blueprint $table) {
                $table->text('address')->nullable()->after('university_name');
            });
        }

        // 2. Migrate existing city and state data into address
        try {
            $colleges = DB::table('colleges')->get();
            foreach ($colleges as $c) {
                $parts = array_filter([$c->city ?? null, $c->state ?? null]);
                if (!empty($parts) && empty($c->address)) {
                    DB::table('colleges')->where('id', $c->id)->update([
                        'address' => implode(', ', $parts),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if columns already dropped
        }

        // 3. Drop all other unnecessary columns
        $columnsToDrop = [
            'city',
            'state',
            'contact_person',
            'contact_email',
            'contact_phone',
            'default_degree',
        ];

        $existingToDrop = [];
        foreach ($columnsToDrop as $col) {
            if (Schema::hasColumn('colleges', $col)) {
                $existingToDrop[] = $col;
            }
        }

        if (!empty($existingToDrop)) {
            Schema::table('colleges', function (Blueprint $table) use ($existingToDrop) {
                $table->dropColumn($existingToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colleges', function (Blueprint $table) {
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('default_degree')->nullable();
            $table->dropColumn('address');
        });
    }
};
