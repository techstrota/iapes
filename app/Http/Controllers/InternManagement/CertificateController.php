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

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : asset('images/TsLogo.png');

        $qrCodes = [];
        foreach ($interns as $intern) {
            $token = $intern->cert_token
                ?: ($intern->completionCertificate?->cert_token
                ?: ($intern->cert_ref_id ?: str_replace(['/', '\\'], '-', $intern->intern_code ?: 'INT-' . $intern->id)));
            $url = route('certificate.verify', ['token' => $token]);
            $svg = app(Generator::class)->size(150)->format('svg')->generate($url);
            $qrCodes[$intern->id] = $svg;

            if ($intern->offerletter) {
                $qrCodes[$intern->offerletter->id] = $svg;
            }
        }

        return [$interns, $offers, $logoBase64, $qrCodes];
    }
    
    // ------------------------------
    // RESOLVE INTERNS (YOU MUST HAVE THIS)
    // ------------------------------

    private function resolveInterns(string $id): \Illuminate\Support\Collection
    {
        $query = Intern::with(['offerletter.intern', 'completionCertificate', 'application', 'team']);

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

    public function viewCertificate(Request $request, string $id, bool $displayOnly = false)
    {
        [$interns, $offers, $logoBase64, $qrCodes] = $this->prepareViewData($id);

        $viewName = view()->exists('certificate.intern-completion-certificate')
            ? 'certificate.intern-completion-certificate'
            : 'certificate.certificate';

        $isDisplayOnly = $displayOnly || $request->boolean('display_only');

        return response(
            View::make($viewName, [
                'interns'     => $interns,
                'offers'      => $offers,
                'isPdf'       => false,
                'logo'        => $logoBase64,
                'qrCodes'     => $qrCodes,
                'displayOnly' => $isDisplayOnly,
            ])->render(),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function downloadCertificate(Request $request, string $id)
    {
        [$interns, $offers, $logoBase64, $qrCodes] = $this->prepareViewData($id);

        $filename = $interns->count() === 1
            ? 'certificate_' . str_replace(['/', '\\'], '-', $interns->first()->intern_code ?: 'INT-' . $interns->first()->id) . '.pdf'
            : 'certificates_bulk.pdf';

        $viewName = view()->exists('certificate.intern-completion-certificate')
            ? 'certificate.intern-completion-certificate'
            : 'certificate.certificate';

        $html = View::make($viewName, [
            'interns' => $interns,
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
            ->hideBrowserHeaderAndFooter()
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Configures a robust Browsershot instance supporting Windows & Linux environments.
     */
    public static function configureBrowsershot(string $html): Browsershot
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
            ->hideBrowserHeaderAndFooter()
            ->margins(0, 0, 0, 0)
            ->noSandbox()
            ->timeout(180);
    }

    protected function getBrowsershot(string $html): Browsershot
    {
        return self::configureBrowsershot($html);
    }

    public function verifyQR($token = null)
    {
        if (!$token) {
            abort(404, 'Invalid verification link.');
        }

        $candidates = array_unique(array_filter([
            $token,
            str_replace('-', '/', $token),
            str_replace('/', '-', $token),
            str_replace('CERT-', '', $token),
            str_replace('CERT-', '', str_replace('-', '/', $token)),
            'CERT-' . str_replace('CERT-', '', $token),
            'CERT-' . str_replace('CERT-', '', str_replace('/', '-', $token)),
        ]));

        // 1. Try to find in CompletionCertificate model
        $compCert = \App\Models\InternManagement\CompletionCertificate::whereIn('cert_token', $candidates)
            ->orWhereIn('cert_ref_id', $candidates)
            ->orWhereIn('intern_code', $candidates)
            ->first();

        if ($compCert) {
            // Display only - no automatic download
            return $this->viewCertificate(request(), (string) $compCert->intern_id, true);
        }

        // 2. Try to find in Intern model
        $intern = \App\Models\InternManagement\Intern::whereIn('cert_token', $candidates)
            ->orWhereIn('cert_ref_id', $candidates)
            ->orWhereIn('intern_code', $candidates)
            ->first();

        if ($intern) {
            // Display only - no automatic download
            return $this->viewCertificate(request(), (string) $intern->id, true);
        }

        // 3. Fallback: Try to find the token in the ManualCertificate model
        $manualCert = \App\Models\InternManagement\ManualCertificate::whereIn('cert_token', $candidates)
            ->orWhereIn('certificate_number', $candidates)
            ->first();

        if ($manualCert) {
            // Display manual certificate inline (isStream = true)
            return ManualCertificateResource::downloadSinglePdf($manualCert, true);
        }

        // 4. Fallback: Try to find the token in the EventRegistration model
        $eventReg = \App\Models\EventRegistration::whereIn('certificate_number', $candidates)->first();

        if ($eventReg) {
            // Display event certificate inline
            return \App\Filament\Resources\EventManagement\EventRegistrationResource::downloadSinglePdf($eventReg, true);
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
