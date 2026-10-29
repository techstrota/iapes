<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InternManagement\Intern;
use App\Models\ReportManagement\College;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up any universities or invalid non-college entries that might have been inserted
        DB::table('colleges')
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(name)) IN (?, ?, ?)', ['spu', 'techstrota', 'kutch univercity'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%university%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%univercity%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%vishwavidyalaya%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%vidhyapith%']);
            })
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('college_evaluations')
                    ->whereColumn('college_evaluations.college_id', 'colleges.id');
            })
            ->delete();

        // 2. Sync from offer_letters (Strictly real colleges only, never universities)
        $offers = DB::table('offer_letters')
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->select('college', 'university', 'degree')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($offers as $offer) {
            $collegeName = trim((string) $offer->college);
            if ($collegeName === '' || OfferLetter::isUniversityOrInvalidCollege($collegeName)) {
                continue;
            }

            $univ = filled($offer->university) ? trim((string) $offer->university) : null;
            $deg  = filled($offer->degree) ? trim((string) $offer->degree) : null;

            $existing = DB::table('colleges')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($collegeName)])
                ->first();

            if (!$existing) {
                DB::table('colleges')->insert([
                    'name'            => $collegeName,
                    'university_name' => $univ,
                    'default_degree'  => $deg,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            } else {
                $updates = [];
                if (empty($existing->university_name) && filled($univ)) {
                    $updates['university_name'] = $univ;
                }
                if (empty($existing->default_degree) && filled($deg)) {
                    $updates['default_degree'] = $deg;
                }
                if (!empty($updates)) {
                    $updates['updated_at'] = now();
                    DB::table('colleges')->where('id', $existing->id)->update($updates);
                }
            }
        }

        // 3. Sync from interns (Strictly real colleges only, never universities)
        $interns = DB::table('interns')
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->select('college', 'university', 'degree')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($interns as $intern) {
            $collegeName = trim((string) $intern->college);
            if ($collegeName === '' || OfferLetter::isUniversityOrInvalidCollege($collegeName)) {
                continue;
            }

            $univ = filled($intern->university) ? trim((string) $intern->university) : null;
            $deg  = filled($intern->degree) ? trim((string) $intern->degree) : null;

            $existing = DB::table('colleges')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($collegeName)])
                ->first();

            if (!$existing) {
                DB::table('colleges')->insert([
                    'name'            => $collegeName,
                    'university_name' => $univ,
                    'default_degree'  => $deg,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            } else {
                $updates = [];
                if (empty($existing->university_name) && filled($univ)) {
                    $updates['university_name'] = $univ;
                }
                if (empty($existing->default_degree) && filled($deg)) {
                    $updates['default_degree'] = $deg;
                }
                if (!empty($updates)) {
                    $updates['updated_at'] = now();
                    DB::table('colleges')->where('id', $existing->id)->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive: existing colleges remain
    }
};
