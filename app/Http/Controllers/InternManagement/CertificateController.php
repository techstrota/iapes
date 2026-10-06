<?php

namespace App\Http\Controllers\InternManagement;

use App\Http\Controllers\Controller;
use App\Models\InternManagement\Intern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage; // To store files
use Spatie\Browsershot\Browsershot;
use SimpleSoftwareIO\QrCode\Generator;

use App\Filament\Resources\InternManagement\ManualCertificateResource;
use App\Models\InternManagement\ManualCertificate;

class CertificateController extends Controller
{
    // ------------------------------
    // COMMON DATA PREPARATION
    // ------------------------------

    private function prepareViewData(string $id): array
    {
        $interns = $this->resolveInterns($id);
        $offers  = $interns->map(fn($i) => $i->offerletter)->filter();

        $logoBase64 = 'data:image/png;base64,' . base64_encode(
            file_get_contents(public_path('images/TsLogo.png'))
        );

        $qrCodes = $offers->mapWithKeys(function ($offer) {
            $token = str_replace('/', '-', $offer->intern->intern_code);
            $url   = route('certificate.verify', ['token' => $token]);
            $svg   = app(Generator::class)->size(150)->format('svg')->generate($url);
            return [$offer->id => $svg];
        });

        return [$interns, $offers, $logoBase64, $qrCodes];
    }
    
    // ------------------------------
    // RESOLVE INTERNS (YOU MUST HAVE THIS)
    // ------------------------------

    private function resolveInterns(string $id): \Illuminate\Support\Collection
    {
        $query = Intern::with(['offerletter.intern']); // ← eager load the chain

        if ($id === 'all') {
            return $query->get();
        }

        return $query->where('id', $id)->get();

    }

    // --- COMPLETION LETTER METHODS ---

    public function viewCompletionLetter(Request $request, string $id)
    {
        $interns = $this->resolveInterns($id);
        // Pre-encode image as base64 — no file fetch needed by DomPDF
        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        if ($interns->count() === 1) {
            $intern = $interns->first();
            $template = $intern->completion_letter_template ?? 'bachelors';
            return response(
                View::make("completionletter.{$template}", ['intern' => $intern, 'isPdf' => false, 'logo'   => $logoBase64,])->render(),
                200, ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        return response(
            View::make('completionletter.bulk', ['interns' => $interns, 'isPdf' => false, 'logo'   => $logoBase64,])->render(),
            200, ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function downloadCompletionLetter(Request $request, string $id)
    {
        [$interns, $offers, $logoBase64, $qrCodes] = $this->prepareViewData($id);

        $filename = $interns->count() === 1
            ? 'completion_letter_' . str_replace(['/', '\\'], '-', $interns->first()->intern_code) . '.pdf'
            : 'completion_letters_bulk.pdf';

        $html = View::make('completionletter.bulk', [
            'offers'  => $offers,
            'isPdf'   => true,
            'logo'    => $logoBase64,
            'interns' => $interns,
        ])->render();

        $pdf = $this->getBrowsershot($html)
            ->format('A4')
            ->showBackground()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    // --- CERTIFICATE METHODS ---

    public function viewCertificate(Request $request, string $id)
    {
        [$interns, $offers, $logoBase64, $qrCodes] = $this->prepareViewData($id);

        return response(
            View::make('certificate.certificate', [
                'offers'   => $offers,
                'isPdf'    => false,
                'logo'     => $logoBase64,
                'qrCodes'  => $qrCodes,
            ])->render(),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function downloadCertificate(Request $request, string $id)
    {
        [$interns, $offers, $logoBase64, $qrCodes] = $this->prepareViewData($id);

        $filename = $interns->count() === 1
            ? 'certificate_' . str_replace(['/', '\\'], '-', $interns->first()->intern_code) . '.pdf'
            : 'certificates_bulk.pdf';

        $html = View::make('certificate.certificate', [
            'offers'  => $offers,
            'isPdf'   => true,
            'logo'    => $logoBase64,
            'qrCodes' => $qrCodes,
        ])->render();

        $pdf = $this->getBrowsershot($html)
            ->format('A4')
            ->landscape()
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Configures a robust Browsershot instance supporting Windows & Linux environments.
     */
    protected function getBrowsershot(string $html): Browsershot
    {
        $browsershot = Browsershot::html($html);

        $nodePath = env('NODE_PATH') ?: (PHP_OS_FAMILY === 'Windows' ? 'C:\Program Files\nodejs\node.exe' : '/usr/bin/node');
        if (is_string($nodePath) && file_exists($nodePath)) {
            $browsershot->setNodeBinary($nodePath);
        }

        $npmPath = env('NPM_PATH') ?: (PHP_OS_FAMILY === 'Windows' ? 'C:\Program Files\nodejs\npm.cmd' : '/usr/bin/npm');
        if (is_string($npmPath) && file_exists($npmPath)) {
            $browsershot->setNpmBinary($npmPath);
        }

        $chromePath = env('CHROME_PATH');
        if ($chromePath && is_string($chromePath) && file_exists($chromePath)) {
            $browsershot->setChromePath($chromePath);
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $possibleChrome = [
                'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
                'C:\Program Files\Google\Chrome\Application\chrome.exe',
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
                'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
            ];
            foreach ($possibleChrome as $path) {
                if (file_exists($path)) {
                    $browsershot->setChromePath($path);
                    break;
                }
            }
        }

        return $browsershot
            ->noSandbox()
            ->timeout(120);
    }

    public function verifyQR($token = null)
    {
        if (!$token) {
            abort(404, 'Invalid verification link.');
        }

        // 1. Try to find in CompletionCertificate model (by cert_token or cert_ref_id)
        $compCert = \App\Models\InternManagement\CompletionCertificate::where('cert_token', $token)
            ->orWhere('cert_ref_id', $token)
            ->first();

        if ($compCert) {
            return $this->downloadCertificate(request(), (string) $compCert->intern_id);
        }

        // 2. Try to find the token in the Intern model
        $intern = \App\Models\InternManagement\Intern::where('cert_token', $token)
            ->orWhere('cert_ref_id', $token)
            ->first();

        if ($intern) {
            // If found in Interns, use your existing certificate download logic
            return $this->downloadCertificate(request(), (string) $intern->id);
        }

        // 2. Fallback: Try to find the token in the ManualCertificate model
        $manualCert = \App\Models\InternManagement\ManualCertificate::where('cert_token', $token)->first();

        if ($manualCert) {
            // If found in ManualCertificates, use the Manual Resource logic
            return ManualCertificateResource::downloadSinglePdf($manualCert, false);
        }

        // 3. Fallback: Try to find the token in the EventRegistration model
        $eventReg = \App\Models\EventRegistration::where('certificate_number', $token)->first();

        if ($eventReg) {
            // If found in EventRegistration, download the event certificate
            return \App\Filament\Resources\EventManagement\EventRegistrationResource::downloadSinglePdf($eventReg);
        }

        abort(404, 'Certificate not found.');
    }

    public function saveCertificateToServer(string $id)
    {
        $interns = $this->resolveInterns($id);
        $offers = $interns->map(fn($i) => $i->offerletter)->filter();

        if ($offers->isEmpty()) {
            return back()->with('error', 'No certificate data found.');
        }

        foreach ($offers as $offer) {
            $internCode = $offer->intern->intern_code;
            // Clean the filename (replace / with - to avoid directory issues)
            $safeFileName = str_replace('/', '-', $internCode) . '.html';
            $path = "certificates/{$safeFileName}";

            // Render the HTML content
            $html = View::make('certificate.certificate', [
                'offers' => collect([$offer]),
                'isPdf'  => false,
            ])->render();

            // Save to storage/app/public/certificates/
            Storage::disk('public')->put($path, $html);
        }

        return back()->with('success', 'Certificates saved to server successfully.');
    }
}
