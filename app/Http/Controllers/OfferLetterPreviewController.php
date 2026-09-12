<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\Application;

class OfferLetterPreviewController extends Controller
{
    /**
     * POST /admin/offer-letters/preview
     * Accepts form data and renders the offer letter template as HTML (not PDF).
     */
    public function preview(Request $request)
    {
        $template = $request->input('template', 'general');

        // Whitelist to prevent template injection
        $allowedTemplates = ['3_month_offer_letter', '4_month_offer_letter', '6_month_offer_letter', 'one_month', 'general'];
        if (!in_array($template, $allowedTemplates)) {
            $template = 'general';
        }

        $name         = trim((string) $request->input('name', '')) ?: 'Candidate Name';
        $college      = trim((string) $request->input('college', '')) ?: 'College / Institute Name';
        $university   = trim((string) $request->input('university', '')) ?: $college;
        $degree       = trim((string) $request->input('degree', '')) ?: 'B.Tech / B.C.A.';
        $email        = trim((string) $request->input('email', '')) ?: 'candidate@example.com';
        $phone        = trim((string) $request->input('phone', '')) ?: '+91 98765 43210';
        $role         = trim((string) $request->input('internship_role', '')) ?: 'Web Developer';
        $position     = trim((string) $request->input('internship_position', '')) ?: ($role . ' Intern');
        $workingHours = trim((string) $request->input('working_hours', '')) ?: '42 hours per week';
        $joiningDate  = $request->input('joining_date') ?: now()->addDays(7)->toDateString();
        $completionDate = $request->input('completion_date') ?: now()->addMonths(4)->toDateString();
        $issueDate    = $request->input('offer_issue_date') ?: now()->toDateString();
        $description  = $request->input('description', '');
        if (empty(trim(strip_tags($description))) && in_array($template, ['4_month_offer_letter', '6_month_offer_letter'])) {
            $description = OfferLetter::defaultDescription($joiningDate, $completionDate, $workingHours);
        }

        // Fake Application instance
        $fakeApp = new Application([
            'name'             => $name,
            'college'          => $college,
            'degree'           => $degree,
            'email'            => $email,
            'phone'            => $phone,
            'domain'           => $role,
            'application_code' => 'APP/' . now()->format('dmy') . '/PREVIEW',
        ]);
        $fakeApp->syncOriginal();

        // Fake OfferLetter instance
        $fakeOffer = new OfferLetter();
        $fakeOffer->fill([
            'name'                => $name,
            'college'             => $college,
            'university'          => $university,
            'degree'              => $degree,
            'email'               => $email,
            'phone'               => $phone,
            'internship_role'     => $role,
            'internship_position' => $position,
            'working_hours'       => $workingHours,
            'joining_date'        => $joiningDate,
            'completion_date'     => $completionDate,
            'offer_issue_date'    => $issueDate,
            'description'         => $description,
            'offer_letter_code'   => 'OFFER/' . now()->year . '/PREVIEW',
            'template'            => $template,
            'offer_status'        => 'draft',
        ]);
        $fakeOffer->setRelation('application', $fakeApp);
        $fakeOffer->syncOriginal();

        $renderedHtml = view("offerletter.{$template}", [
            'offers'    => collect([$fakeOffer]),
            'isPreview' => true,
        ])->render();

        // Convert public_path image URLs to asset/web URLs for in-browser rendering
        $publicPath = str_replace('\\', '/', public_path());
        $renderedHtml = str_replace([
            $publicPath . '/',
            public_path() . DIRECTORY_SEPARATOR,
            public_path() . '/',
        ], url('/') . '/', $renderedHtml);

        // Inject preview container styles so header/footer and page-breaks look natural in an iframe
        $previewEnhancements = <<<HTML
<style>
    body {
        background-color: #f8fafc !important;
        padding: 20px !important;
        color: #1e293b !important;
    }
    .main-content, body > div, header, footer {
        max-width: 800px;
        margin-left: auto !important;
        margin-right: auto !important;
    }
    header {
        position: static !important;
        margin-bottom: 20px;
    }
    footer {
        position: static !important;
        margin-top: 30px;
    }
    .signature-section {
        position: static !important;
        margin-top: 40px;
    }
    .date-section {
        margin-top: 10px !important;
    }
</style>
HTML;

        $renderedHtml = str_replace('</head>', $previewEnhancements . '</head>', $renderedHtml);

        return response($renderedHtml, 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
