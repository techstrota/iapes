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
