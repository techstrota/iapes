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
        // 1. Create colleges master table
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('university_name')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('default_degree')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        // 2. Add batch_title, duration, and college_id to college_evaluations table
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->after('id')->constrained('colleges')->nullOnDelete();
            $table->string('batch_title')->nullable()->after('college_id');
            $table->string('duration')->nullable()->after('batch_title');
        });

        // 3. Auto-populate colleges table from distinct colleges already in interns table
        try {
            $distinctColleges = DB::table('interns')
                ->whereNotNull('college')
                ->where('college', '!=', '')
                ->select('college', DB::raw('MAX(university) as university_name'), DB::raw('MAX(degree) as default_degree'))
                ->groupBy('college')
                ->get();

            foreach ($distinctColleges as $item) {
                DB::table('colleges')->insert([
                    'name'            => trim($item->college),
                    'university_name' => trim($item->university_name ?? ''),
                    'default_degree'  => trim($item->default_degree ?? ''),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if interns table has non-groupable data
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_evaluations', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn(['college_id', 'batch_title', 'duration']);
        });

        Schema::dropIfExists('colleges');
    }
};
