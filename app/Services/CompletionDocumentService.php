<?php

namespace App\Services;

use App\Models\InternManagement\CompletionCertificate;
use App\Models\InternManagement\CompletionLetter;
use App\Models\InternManagement\Intern;
use Illuminate\Support\Str;

class CompletionDocumentService
{
    /**
     * Generate or update a Completion Certificate for an intern.
     * Snapshots all intern identity + date fields at generation time.
     * Syncs cert_ref_id, cert_token, project_name, grade, issuing_date back to intern.
     */
    public function generateCertificate(Intern $intern, array $data, string $generatedBy = ''): CompletionCertificate
    {
        $code = $intern->intern_code ?: ('INT-' . str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT));
        $certRefId = 'CERT-' . $code;
        $token = $intern->cert_token ?: Str::random(64);

        $cert = CompletionCertificate::updateOrCreate(
            ['intern_id' => $intern->id],
            [
                'cert_ref_id'     => $certRefId,
                'cert_token'      => $token,

                // Intern identity snapshot
                'intern_name'     => $intern->name ?: ($intern->offerletter?->name ?? $intern->application?->name),
                'intern_code'     => $code,
                'internship_role' => $data['internship_role'] ?? ($intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Software Development')),

                // Dates (from form or fallback)
                'joining_date'    => !empty($data['joining_date']) ? $data['joining_date'] : ($intern->joining_date ?: $intern->offerletter?->joining_date),
                'completion_date' => !empty($data['completion_date']) ? $data['completion_date'] : ($intern->completion_date ?: $intern->offerletter?->completion_date),
                'issuing_date'    => !empty($data['issuing_date']) ? $data['issuing_date'] : now()->toDateString(),

                // Evaluation (from form)
                'project_name'    => $data['project_name'] ?? ($intern->team?->team_name ?: ($intern->project_name ?: 'Project')),
                'grade'           => $data['grade'] ?? ($intern->grade ?: 'A'),

                // Audit
                'generated_by'    => $generatedBy ?: (auth()->user()?->email ?? 'System Admin'),
                'generated_at'    => now(),
            ]
        );

        // Sync key fields back to intern record
        $internUpdates = [
            'cert_ref_id'  => $cert->cert_ref_id,
            'cert_token'   => $cert->cert_token,
            'project_name' => $cert->project_name,
            'grade'        => $cert->grade,
            'issuing_date' => $cert->issuing_date,
        ];

        if (!empty($data['completion_letter_template'])) {
            $internUpdates['completion_letter_template'] = $data['completion_letter_template'];
        }
        if (!empty($cert->joining_date) && empty($intern->joining_date)) {
            $internUpdates['joining_date'] = $cert->joining_date;
        }
        if (!empty($cert->completion_date) && empty($intern->completion_date)) {
            $internUpdates['completion_date'] = $cert->completion_date;
        }
        if (!empty($cert->internship_role) && empty($intern->internship_role)) {
            $internUpdates['internship_role'] = $cert->internship_role;
        }

        $intern->update($internUpdates);

        return $cert;
    }

    /**
     * Generate or update a Completion Letter for an intern.
     * Snapshots all intern identity, academic, and date fields at generation time.
     * Syncs letter_ref_id, template, and editable fields back to intern.
     */
    public function generateLetter(Intern $intern, array $data, string $generatedBy = ''): CompletionLetter
    {
        $code = $intern->intern_code ?: ('INT-' . str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT));
        $letterRefId = 'LET-' . $code;

        $letter = CompletionLetter::updateOrCreate(
            ['intern_id' => $intern->id],
            [
                'letter_ref_id'      => $letterRefId,
                'template'           => $data['template'] ?? ($intern->completion_letter_template ?: 'bachelors'),

                // Intern identity snapshot
                'intern_name'        => $intern->name ?: ($intern->offerletter?->name ?? $intern->application?->name),
                'intern_code'        => $code,
                'internship_role'    => $data['internship_role'] ?? ($intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Software Development')),
                'degree'             => $data['degree'] ?? ($intern->degree ?: ($intern->offerletter?->degree ?? $intern->application?->degree)),
                'college'            => $data['college'] ?? ($intern->college ?: ($intern->offerletter?->college ?? $intern->application?->college)),
                'university'         => $data['university'] ?? ($intern->university ?: ($intern->offerletter?->university ?? $intern->college)),

                // Dates
                'joining_date'       => !empty($data['joining_date']) ? $data['joining_date'] : ($intern->joining_date ?: $intern->offerletter?->joining_date),
                'completion_date'    => !empty($data['completion_date']) ? $data['completion_date'] : ($intern->completion_date ?: $intern->offerletter?->completion_date),
                'issuing_date'       => !empty($data['issuing_date']) ? $data['issuing_date'] : now()->toDateString(),

                // Evaluation & content
                'project_name'       => $data['project_name'] ?? ($intern->team?->team_name ?: ($intern->project_name ?: 'Project')),
                'grade'              => $data['grade'] ?? ($intern->grade ?: 'A'),
                'project_description'=> $data['project_description'] ?? ($intern->team?->project_description ?: ($intern->project_description ?: null)),
                'working_hours'      => $data['working_hours'] ?? ($intern->working_hours ?: '42 hours per week'),

                // Audit
                'generated_by'       => $generatedBy ?: (auth()->user()?->email ?? 'System Admin'),
                'generated_at'       => now(),
            ]
        );

        // Sync key fields back to intern record
        $internUpdates = [
            'letter_ref_id'              => $letter->letter_ref_id,
            'completion_letter_template' => $letter->template,
            'project_name'               => $letter->project_name,
            'grade'                      => $letter->grade,
            'issuing_date'               => $letter->issuing_date,
        ];

        if (!empty($letter->project_description)) {
            $internUpdates['project_description'] = $letter->project_description;
        }
        if (!empty($letter->working_hours)) {
            $internUpdates['working_hours'] = $letter->working_hours;
        }
        if (!empty($letter->joining_date) && empty($intern->joining_date)) {
            $internUpdates['joining_date'] = $letter->joining_date;
        }
        if (!empty($letter->completion_date) && empty($intern->completion_date)) {
            $internUpdates['completion_date'] = $letter->completion_date;
        }
        if (!empty($letter->internship_role) && empty($intern->internship_role)) {
            $internUpdates['internship_role'] = $letter->internship_role;
        }
        if (!empty($letter->degree) && empty($intern->degree)) {
            $internUpdates['degree'] = $letter->degree;
        }
        if (!empty($letter->college) && empty($intern->college)) {
            $internUpdates['college'] = $letter->college;
        }
        if (!empty($letter->university) && empty($intern->university)) {
            $internUpdates['university'] = $letter->university;
        }

        $intern->update($internUpdates);

        return $letter;
    }
}
