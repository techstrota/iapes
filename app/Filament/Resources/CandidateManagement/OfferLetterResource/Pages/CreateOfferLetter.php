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
        $description = !empty(trim(strip_tags($data['description'] ?? '')))
            ? $data['description']
            : (in_array($data['template'], ['4_month_offer_letter', '6_month_offer_letter'])
                ? OfferLetter::defaultDescription($data['joining_date'], $data['completion_date'], $data['working_hours'] ?? '42 hours per week')
                : null);

        // ── GENERAL FLOW: no application selected ──────────────────────────
        if (empty($data['applications'] ?? null)) {
            return OfferLetter::create([
                'application_id'      => null,
                'offer_status'        => 'draft',
                'is_accepted'         => false,
                'name'                => $data['name'] ?? null,
                'university'          => $data['university'] ?? null,
                'college'             => $data['college'] ?? null,
                'phone'               => $data['phone'] ?? null,
                'email'               => $data['email'] ?? null,
                'joining_date'        => $data['joining_date'],
                'completion_date'     => $data['completion_date'],
                'internship_role'     => $data['internship_role'],
                'internship_position' => $data['internship_position'],
                'working_hours'       => $data['working_hours'] ?? '42 hours per week',
                'template'            => $data['template'],
                'description'         => $description,
                'offer_issue_date'    => $data['offer_issue_date'] ?? now()->toDateString(),
            ]);
        }

        // ── APPLICATION-BASED FLOW: bulk create one letter per intern ──────
        $applicationIds   = $data['applications'];
        $editedName       = $data['name'];
        $editedUniversity = $data['university'];
        $editedCollege    = $data['college'];
        $editedphone      = $data['phone'] ?? null;
        $editedemail      = $data['email'] ?? null;

        $lastCreatedRecord = null;

        foreach ($applicationIds as $id) {
            $application = Application::find($id);

            if ($application) {
                $application->update([
                    'name'   => $editedName,
                    'college' => $editedCollege,
                ]);
            }

            $lastCreatedRecord = OfferLetter::create([
                'application_id'      => $id,
                'offer_status'        => 'draft',
                'is_accepted'         => false,
                'name'                => $editedName,
                'college'             => $editedCollege,
                'university'          => $editedUniversity,
                'phone'               => $editedphone,
                'email'               => $editedemail,
                'joining_date'        => $data['joining_date'],
                'completion_date'     => $data['completion_date'],
                'internship_role'     => $data['internship_role'],
                'internship_position' => $data['internship_position'],
                'working_hours'       => $data['working_hours'] ?? '42 hours per week',
                'template'            => $data['template'],
                'intern_id'           => $application->intern_id ?? null,
                'description'         => $description,
                'offer_issue_date'    => $data['offer_issue_date'] ?? now()->toDateString(),
            ]);
        }

        return $lastCreatedRecord;
    }
    
}
