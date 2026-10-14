<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;

use App\Filament\Resources\CandidateManagement\OfferLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\Application;
use Illuminate\Database\Eloquent\Model;

class CreateOfferLetter extends CreateRecord
{
    protected static string $resource = OfferLetterResource::class;
    protected static string $view = 'filament.offer-letters.split-pane';

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $templatePresetId = $data['offer_letter_template_id'] ?? null;
        $template = $data['template'] ?? 'single_page';
        $duration = $data['duration'] ?? '1 month';
        $description = $template === 'multi_page'
            ? (!empty(trim(strip_tags($data['description'] ?? '')))
                ? $data['description']
                : OfferLetter::defaultDescription($data['joining_date'], $data['completion_date'], $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday'))
            : null;

        // ── GENERAL FLOW: no application selected ──────────────────────────
        if (empty($data['applications'] ?? null)) {
            return OfferLetter::create([
                'application_id'           => null,
                'offer_letter_template_id' => $templatePresetId,
                'offer_status'             => 'draft',
                'is_accepted'              => false,
                'name'                     => $data['name'] ?? null,
                'university'               => $data['university'] ?? null,
                'college'                  => $data['college'] ?? null,
                'degree'                   => $data['degree'] ?? null,
                'phone'                    => $data['phone'] ?? null,
                'email'                    => $data['email'] ?? null,
                'joining_date'             => $data['joining_date'],
                'completion_date'          => $data['completion_date'],
                'internship_role'          => $data['internship_role'],
                'internship_position'      => $data['internship_position'],
                'working_hours'            => $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday',
                'duration'                 => $duration,
                'template'                 => $template,
                'description'              => $description,
                'offer_issue_date'         => $data['offer_issue_date'] ?? now()->toDateString(),
            ]);
        }

        // ── APPLICATION-BASED FLOW: bulk create one letter per candidate ──────
        $applicationIds    = $data['applications'];
        $isSingle          = count($applicationIds) === 1;
        $lastCreatedRecord = null;
        $createdCount      = 0;

        foreach ($applicationIds as $id) {
            $application = Application::find($id);
            if (!$application) {
                continue;
            }

            // For single candidate, allow using any edited values from form.
            // For multiple candidates, preserve each candidate's own application details!
            $candidateName       = $isSingle ? ($data['name'] ?? $application->name) : $application->name;
            $candidateCollege    = $isSingle ? ($data['college'] ?? $application->college) : $application->college;
            $candidateUniversity = $isSingle ? ($data['university'] ?? ($application->college ?? '')) : ($application->college ?? '');
            $candidateDegree     = $isSingle ? ($data['degree'] ?? $application->degree) : $application->degree;
            $candidatePhone      = $isSingle ? ($data['phone'] ?? $application->phone) : $application->phone;
            $candidateEmail      = $isSingle ? ($data['email'] ?? $application->email) : $application->email;

            if ($isSingle) {
                $application->update([
                    'name'    => $candidateName,
                    'college' => $candidateCollege,
                ]);
            }

            $offerPayload = [
                'application_id'           => $id,
                'offer_letter_template_id' => $templatePresetId,
                'offer_status'             => 'draft',
                'is_accepted'              => false,
                'name'                     => $candidateName,
                'college'                  => $candidateCollege,
                'university'               => $candidateUniversity,
                'degree'                   => $candidateDegree,
                'phone'                    => $candidatePhone,
                'email'                    => $candidateEmail,
                'joining_date'             => $data['joining_date'],
                'completion_date'          => $data['completion_date'],
                'internship_role'          => $data['internship_role'],
                'internship_position'      => $data['internship_position'],
                'working_hours'            => $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday',
                'duration'                 => $duration,
                'template'                 => $template,
                'intern_id'                => $application->intern_id ?? null,
                'description'              => $description,
                'offer_issue_date'         => $data['offer_issue_date'] ?? now()->toDateString(),
            ];

            $existingDraft = OfferLetter::where('application_id', $id)
                ->where('offer_status', 'draft')
                ->first();

            if ($existingDraft) {
                $existingDraft->update($offerPayload);
                $lastCreatedRecord = $existingDraft;
            } else {
                $lastCreatedRecord = OfferLetter::create($offerPayload);
            }

            $createdCount++;
        }

        if ($createdCount > 1) {
            \Filament\Notifications\Notification::make()
                ->title("Generated {$createdCount} Draft Offer Letters")
                ->body("Created/updated offer letters for all {$createdCount} selected candidates using the template details.")
                ->success()
                ->send();
        }

        return $lastCreatedRecord;
    }
    
}
