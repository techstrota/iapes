<?php

use Illuminate\Support\Facades\Route;
use App\Models\InterviewManagement\OfferLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InternManagement\Intern;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\InternManagement\CertificateController;

use App\Filament\Resources\InternManagement\ManualCertificateResource;
use App\Models\InternManagement\ManualCertificate;
use App\Filament\Resources\EventManagement\EventRegistrationResource;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Http\Controllers\ReportController;

Route::get('/view-offer-pdf/{id}', function ($id) {
    // Find by ID - this is much more reliable
    $record = OfferLetter::findOrFail($id);

    $template = $record->template ?? 'general';

    $pdf = Pdf::loadView("offerletter.$template", [
        'offers' => collect([$record])
    ]);

    // We still use the code for the filename, but ID for the URL
    $fileName = str_replace('/', '-', $record->id);

    return $pdf->stream($fileName . '.pdf');
})->name('view-offer-pdf')->middleware(['auth:web,intern']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('filament.intern.auth.login');
})->name('login');

//---------------------------------------------------------


Route::get('/view-completion-pdf/{id}', function ($id) {
    $intern = \App\Models\InternManagement\Intern::with(['offerletter.application'])->findOrFail($id);
    $offer  = $intern->offerletter;

    // Map template slugs → actual blade paths
    $templateMap = [
        '3_month_offer_letter' => 'completionletter.bachelors',
        'masters_offer_letter' => 'completionletter.masters',
        // add more as needed
    ];

    $view = $templateMap[$offer->template] 
            ?? 'completionletter.bachelors'; // fallback

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, ['offers' => [$offer]]);
    return $pdf->stream('completion_letter.pdf');
})->middleware(['web', 'auth:web,intern'])->name('view-completion-pdf');

//---------------------------------------------------------

Route::middleware(['auth:web,intern'])->group(function () {
    // Completion Letter Routes
    Route::get('/intern/completion-letter/view/{id}', [CertificateController::class, 'viewCompletionLetter'])   
        ->name('intern.completion_letter.view');
    Route::get('/intern/completion-letter/download/{id}', [CertificateController::class, 'downloadCompletionLetter'])
        ->name('intern.completion_letter.download');

    // Certificate Routes
    Route::get('/intern/certificate/view/{id}', [CertificateController::class, 'viewCertificate'])
        ->name('intern.certificate.view');
    Route::get('/intern/certificate/download/{id}', [CertificateController::class, 'downloadCertificate'])
        ->name('intern.certificate.download');
});

//-------------- I - Card -----------------------------

Route::get('/intern-id-card/{id}', function ($id) {
    $intern = Intern::with('offerletter')->findOrFail($id);

    $idCardPath = public_path('images/I-card-bg.png'); // Double check if it's .png or .jpeg!

if (!file_exists($idCardPath)) {
    return "Error: Image not found at " . $idCardPath;
}

$imageData = base64_encode(file_get_contents($idCardPath));
$base64Image = 'data:image/jpeg;base64,' . $imageData;

$internImageBase64 = null;

if ($intern->intern_image && Storage::disk('public')->exists($intern->intern_image)) {
    $path = storage_path('app/public/' . $intern->intern_image);
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $internImageBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
} else {
    // Fallback to a placeholder image if needed
    $internImageBase64 = "path_to_default_avatar_base64_or_null";
}

// Return the VIEW directly, not the PDF stream
    return view('i-card.intern-id-card', [
        'intern' => $intern,
        'base64Image' => $base64Image,
        'internImageBase64' => $internImageBase64,
    ]);
})->name('print-id-card')->middleware(['auth:web,intern']);

// The route that the QR code will point to
Route::get('/verify-certificate/{token}', [CertificateController::class, 'verifyQR'])
     ->name('certificate.verify');

//------------------ Manual Certificate -------------------------
Route::get('/certificates/print/{record}', function (ManualCertificate $record) {
    // This calls the helper method we added to your Resource earlier
    return ManualCertificateResource::downloadSinglePdf($record, $isStream = true);
})->name('certificate.print')->middleware(['auth']); // Ensure only logged-in users can print

//--------------------------------------------------------------------------------------------------------------

//------------------ Event Certificate -------------------------
// Changed URI to /certificates/event/print/{record}
Route::get('/certificates/event/print/{record}', function (Event $record) {
    return EventRegistrationResource::downloadSinglePdf($record, $isStream = true);
})->name('certificate.print')->middleware(['auth']);
// The route that the QR code will point to
Route::get('/verify-certificate/{certificate_number}', [CertificateController::class, 'verifyQR'])
     ->name('certificate.verify');

//--------------------------------------------------------------------------------------------------------------


// ─── Report Download ─────────────────────────────────────────────────────────
Route::get('/reports/download', [ReportController::class, 'download'])
    ->name('report.download')
    ->middleware(['auth:web']);