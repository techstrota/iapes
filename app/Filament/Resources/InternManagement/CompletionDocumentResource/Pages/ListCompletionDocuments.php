<?php

namespace App\Filament\Resources\InternManagement\CompletionDocumentResource\Pages;

use App\Filament\Resources\InternManagement\CompletionDocumentResource;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Services\CompletionDocumentService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListCompletionDocuments extends Page
{
    use WithPagination;

    protected static string $resource = CompletionDocumentResource::class;

    protected static string $view = 'filament.intern-management.completion-documents.list-completion-documents';

    protected static ?string $title = 'Completion Certificates & Letters';

    public string $search = '';
    public string $viewMode = 'all'; // 'all', 'current', 'archived'
    public string $docFilter = 'all'; // 'all', 'has_cert', 'no_cert', 'has_letter', 'no_letter'
    public ?int $selectedBatchId = null;
    public int $perPage = 12;

    // Bulk Selection State
    public array $selectedInternIds = [];

    // Certificate Modal State
    public bool $showCertModal = false;
    public ?int $certModalInternId = null;
    public array $certForm = [];

    // Letter Modal State
    public bool $showLetterModal = false;
    public ?int $letterModalInternId = null;
    public array $letterForm = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedViewMode(): void
    {
        $this->resetPage();
    }

    public function updatedDocFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBatchId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $currentPageIds = $this->getInternsQuery()->paginate($this->perPage)->pluck('id')->map(fn($id) => (int)$id)->toArray();
        $selectedCount = count(array_intersect($this->selectedInternIds, $currentPageIds));

        if ($selectedCount === count($currentPageIds)) {
            // Deselect visible
            $this->selectedInternIds = array_values(array_diff($this->selectedInternIds, $currentPageIds));
        } else {
            // Select visible
            $this->selectedInternIds = array_values(array_unique(array_merge($this->selectedInternIds, $currentPageIds)));
        }
    }

    public function clearSelection(): void
    {
        $this->selectedInternIds = [];
    }

    public function bulkPrintCertificates(string $format = 'combined')
    {
        if (empty($this->selectedInternIds)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return null;
        }

        $interns = Intern::with(['completionCertificate', 'offerletter', 'team', 'application'])
            ->whereIn('id', $this->selectedInternIds)
            ->get();

        if ($interns->isEmpty()) {
            Notification::make()->title('No valid interns found')->warning()->send();
            return null;
        }

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : asset('images/TsLogo.png');

        if ($format === 'combined') {
            $qrCodes = [];
            foreach ($interns as $i) {
                $token = $i->cert_token
                    ?: ($i->completionCertificate?->cert_token
                    ?: ($i->cert_ref_id ?: str_replace(['/', '\\'], '-', $i->intern_code ?: 'INT-' . $i->id)));
                $url = route('certificate.verify', ['token' => $token]);
                $qrCodes[$i->id] = app(\SimpleSoftwareIO\QrCode\Generator::class)->size(150)->format('svg')->generate($url);
            }

            $html = \Illuminate\Support\Facades\View::make('certificate.intern-completion-certificate', [
                'interns'     => $interns,
                'isPdf'       => true,
                'logo'        => $logoBase64,
                'qrCodes'     => $qrCodes,
                'displayOnly' => false,
            ])->render();

            $pdf = \App\Http\Controllers\InternManagement\CertificateController::configureBrowsershot($html)
                ->format('A4')
                ->landscape()
                ->showBackground()
                ->margins(0, 0, 0, 0)
                ->hideBrowserHeaderAndFooter()
                ->pdf();

            return response()->streamDownload(
                fn () => print($pdf),
                'certificates_combined_' . now()->format('Ymd_His') . '.pdf'
            );
        } else {
            // Separate PDFs packaged in a ZIP
            $zipFileName = 'certificates_separate_' . now()->format('Ymd_His') . '.zip';
            $zipPath = storage_path('app/' . $zipFileName);
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                foreach ($interns as $i) {
                    $token = $i->cert_token
                        ?: ($i->completionCertificate?->cert_token
                        ?: ($i->cert_ref_id ?: str_replace(['/', '\\'], '-', $i->intern_code ?: 'INT-' . $i->id)));
                    $url = route('certificate.verify', ['token' => $token]);
                    $svg = app(\SimpleSoftwareIO\QrCode\Generator::class)->size(150)->format('svg')->generate($url);

                    $html = \Illuminate\Support\Facades\View::make('certificate.intern-completion-certificate', [
                        'interns'     => collect([$i]),
                        'isPdf'       => true,
                        'logo'        => $logoBase64,
                        'qrCodes'     => [$i->id => $svg],
                        'displayOnly' => false,
                    ])->render();

                    $pdf = \App\Http\Controllers\InternManagement\CertificateController::configureBrowsershot($html)
                        ->format('A4')
                        ->landscape()
                        ->showBackground()
                        ->margins(0, 0, 0, 0)
                        ->hideBrowserHeaderAndFooter()
                        ->pdf();

                    $safeCode = str_replace(['/', '\\'], '-', $i->intern_code ?: 'INT-' . $i->id);
                    $zip->addFromString("certificate_{$safeCode}.pdf", $pdf);
                }
                $zip->close();
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
    }

    public function bulkPrintLetters(string $format = 'combined')
    {
        if (empty($this->selectedInternIds)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return null;
        }

        $interns = Intern::with(['completionLetter', 'offerletter', 'team', 'application'])
            ->whereIn('id', $this->selectedInternIds)
            ->get();

        if ($interns->isEmpty()) {
            Notification::make()->title('No valid interns found')->warning()->send();
            return null;
        }

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : asset('images/TsLogo.png');

        if ($format === 'combined') {
            $html = \Illuminate\Support\Facades\View::make('completionletter.bulk', [
                'interns' => $interns,
                'isPdf'   => true,
                'logo'    => $logoBase64,
            ])->render();

            $pdf = \App\Http\Controllers\InternManagement\CertificateController::configureBrowsershot($html)
                ->format('A4')
                ->showBackground()
                ->margins(0, 0, 0, 0)
                ->hideBrowserHeaderAndFooter()
                ->pdf();

            return response()->streamDownload(
                fn () => print($pdf),
                'completion_letters_combined_' . now()->format('Ymd_His') . '.pdf'
            );
        } else {
            // Separate PDFs in a ZIP
            $zipFileName = 'completion_letters_separate_' . now()->format('Ymd_His') . '.zip';
            $zipPath = storage_path('app/' . $zipFileName);
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                foreach ($interns as $i) {
                    $template = $i->completion_letter_template ?: 'bachelors';
                    $viewName = view()->exists("completionletter.{$template}") ? "completionletter.{$template}" : 'completionletter.bachelors';

                    $html = \Illuminate\Support\Facades\View::make($viewName, [
                        'intern' => $i,
                        'isPdf'  => true,
                        'logo'   => $logoBase64,
                    ])->render();

                    $pdf = \App\Http\Controllers\InternManagement\CertificateController::configureBrowsershot($html)
                        ->format('A4')
                        ->showBackground()
                        ->margins(0, 0, 0, 0)
                        ->hideBrowserHeaderAndFooter()
                        ->pdf();

                    $safeCode = str_replace(['/', '\\'], '-', $i->intern_code ?: 'INT-' . $i->id);
                    $zip->addFromString("completion_letter_{$safeCode}.pdf", $pdf);
                }
                $zip->close();
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
    }

    public function openCertModal(int $internId): void
    {
        $intern = Intern::with(['completionCertificate', 'offerletter', 'team', 'application'])->findOrFail($internId);
        $cert = $intern->completionCertificate;

        $joiningDate = $cert?->joining_date?->format('Y-m-d')
            ?: ($intern->joining_date?->format('Y-m-d')
            ?: ($intern->offerletter?->joining_date ? \Carbon\Carbon::parse($intern->offerletter->joining_date)->format('Y-m-d') : now()->format('Y-m-d')));

        $completionDate = $cert?->completion_date?->format('Y-m-d')
            ?: ($intern->completion_date?->format('Y-m-d')
            ?: ($intern->offerletter?->completion_date ? \Carbon\Carbon::parse($intern->offerletter->completion_date)->format('Y-m-d') : now()->format('Y-m-d')));

        $issuingDate = $cert?->issuing_date?->format('Y-m-d')
            ?: ($intern->issuing_date?->format('Y-m-d') ?: now()->format('Y-m-d'));

        $this->certModalInternId = $intern->id;
        $this->certForm = [
            'intern_name' => $cert?->intern_name ?: ($intern->name ?: ($intern->offerletter?->name ?? $intern->application?->name)),
            'intern_code' => $cert?->intern_code ?: ($intern->intern_code ?: ('INT-' . str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT))),
            'internship_role' => $cert?->internship_role ?: ($intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Software Development')),
            'joining_date' => $joiningDate,
            'completion_date' => $completionDate,
            'issuing_date' => $issuingDate,
            'project_name' => $cert?->project_name ?: ($intern->team?->team_name ?: ($intern->project_name ?: 'Internship Project')),
            'grade' => $cert?->grade ?: ($intern->grade ?: 'A'),
            'completion_letter_template' => $intern->completion_letter_template ?: 'bachelors',
        ];

        $this->showCertModal = true;
    }

    public function closeCertModal(): void
    {
        $this->showCertModal = false;
        $this->certModalInternId = null;
        $this->certForm = [];
    }

    public function saveCertificate(): void
    {
        if (!$this->certModalInternId) {
            return;
        }

        $intern = Intern::findOrFail($this->certModalInternId);

        $cert = app(CompletionDocumentService::class)->generateCertificate(
            $intern,
            $this->certForm,
            auth()->user()?->email ?? 'Admin'
        );

        $this->closeCertModal();

        Notification::make()
            ->title("Certificate generated ({$cert->cert_ref_id})")
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_cert')
                    ->label('View')
                    ->url(route('intern.certificate.view', ['id' => $intern->id]), shouldOpenInNewTab: true),
                \Filament\Notifications\Actions\Action::make('download_cert')
                    ->label('Download PDF')
                    ->url(route('intern.certificate.download', ['id' => $intern->id]), shouldOpenInNewTab: true),
            ])
            ->persistent()
            ->send();
    }

    public function openLetterModal(int $internId): void
    {
        $intern = Intern::with(['completionLetter', 'offerletter', 'team', 'application'])->findOrFail($internId);
        $letter = $intern->completionLetter;

        $joiningDate = $letter?->joining_date?->format('Y-m-d')
            ?: ($intern->joining_date?->format('Y-m-d')
            ?: ($intern->offerletter?->joining_date ? \Carbon\Carbon::parse($intern->offerletter->joining_date)->format('Y-m-d') : now()->format('Y-m-d')));

        $completionDate = $letter?->completion_date?->format('Y-m-d')
            ?: ($intern->completion_date?->format('Y-m-d')
            ?: ($intern->offerletter?->completion_date ? \Carbon\Carbon::parse($intern->offerletter->completion_date)->format('Y-m-d') : now()->format('Y-m-d')));

        $issuingDate = $letter?->issuing_date?->format('Y-m-d')
            ?: ($intern->issuing_date?->format('Y-m-d') ?: now()->format('Y-m-d'));

        $this->letterModalInternId = $intern->id;
        $this->letterForm = [
            'intern_name' => $letter?->intern_name ?: ($intern->name ?: ($intern->offerletter?->name ?? $intern->application?->name)),
            'intern_code' => $letter?->intern_code ?: ($intern->intern_code ?: ('INT-' . str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT))),
            'internship_role' => $letter?->internship_role ?: ($intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Software Development')),
            'degree' => $letter?->degree ?: ($intern->degree ?: ($intern->offerletter?->degree ?? $intern->application?->degree ?? 'Bachelor of Technology')),
            'college' => $letter?->college ?: ($intern->college ?: ($intern->offerletter?->college ?? $intern->application?->college ?? '')),
            'university' => $letter?->university ?: ($intern->university ?: ($intern->offerletter?->university ?? '')),
            'joining_date' => $joiningDate,
            'completion_date' => $completionDate,
            'issuing_date' => $issuingDate,
            'template' => $letter?->template ?: ($intern->completion_letter_template ?: 'bachelors'),
            'project_name' => $letter?->project_name ?: ($intern->team?->team_name ?: ($intern->project_name ?: 'Internship Project')),
            'grade' => $letter?->grade ?: ($intern->grade ?: 'A'),
            'working_hours' => $letter?->working_hours ?: ($intern->working_hours ?: '42 hours per week'),
            'project_description' => $letter?->project_description ?: ($intern->team?->project_description ?: ($intern->project_description ?: '')),
        ];

        $this->showLetterModal = true;
    }

    public function closeLetterModal(): void
    {
        $this->showLetterModal = false;
        $this->letterModalInternId = null;
        $this->letterForm = [];
    }

    public function saveLetter(): void
    {
        if (!$this->letterModalInternId) {
            return;
        }

        $intern = Intern::findOrFail($this->letterModalInternId);

        $letter = app(CompletionDocumentService::class)->generateLetter(
            $intern,
            $this->letterForm,
            auth()->user()?->email ?? 'Admin'
        );

        $this->closeLetterModal();

        Notification::make()
            ->title("Completion Letter generated ({$letter->letter_ref_id})")
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_letter')
                    ->label('View')
                    ->url(route('intern.completion_letter.view', ['id' => $intern->id]), shouldOpenInNewTab: true),
                \Filament\Notifications\Actions\Action::make('download_letter')
                    ->label('Download PDF')
                    ->url(route('intern.completion_letter.download', ['id' => $intern->id]), shouldOpenInNewTab: true),
            ])
            ->persistent()
            ->send();
    }

    public function getInternsQuery()
    {
        $query = Intern::with(['completionCertificate', 'completionLetter', 'batch', 'team', 'offerletter']);

        if ($this->viewMode === 'current') {
            $query->where('is_active', true);
        } elseif ($this->viewMode === 'archived') {
            $query->where('is_active', false);
        }

        if ($this->docFilter === 'has_cert') {
            $query->whereNotNull('cert_ref_id');
        } elseif ($this->docFilter === 'no_cert') {
            $query->whereNull('cert_ref_id');
        } elseif ($this->docFilter === 'has_letter') {
            $query->whereNotNull('letter_ref_id');
        } elseif ($this->docFilter === 'no_letter') {
            $query->whereNull('letter_ref_id');
        }

        if ($this->selectedBatchId) {
            $query->where('internship_batch_id', $this->selectedBatchId);
        }

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('intern_code', 'like', $term)
                    ->orWhere('cert_ref_id', 'like', $term)
                    ->orWhere('letter_ref_id', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('project_name', 'like', $term)
                    ->orWhereHas('batch', fn ($b) => $b->where('batch_name', 'like', $term))
                    ->orWhereHas('team', fn ($t) => $t->where('team_name', 'like', $term));
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function getViewData(): array
    {
        $interns = $this->getInternsQuery()->paginate($this->perPage);

        $totalInterns = Intern::count();
        $totalCerts = Intern::whereNotNull('cert_ref_id')->count();
        $totalLetters = Intern::whereNotNull('letter_ref_id')->count();
        $bothGenerated = Intern::whereNotNull('cert_ref_id')->whereNotNull('letter_ref_id')->count();

        return [
            'interns' => $interns,
            'batches' => InternshipBatch::all(),
            'stats' => [
                'total' => $totalInterns,
                'certs' => $totalCerts,
                'letters' => $totalLetters,
                'both' => $bothGenerated,
            ],
        ];
    }
}
