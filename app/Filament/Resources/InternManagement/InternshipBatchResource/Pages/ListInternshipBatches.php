<?php

namespace App\Filament\Resources\InternManagement\InternshipBatchResource\Pages;

use App\Filament\Resources\InternManagement\InternshipBatchResource;
use App\Models\InternManagement\InternshipBatch;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInternshipBatches extends ListRecords
{
    protected static string $resource = InternshipBatchResource::class;

    protected static string $view = 'filament.intern-management.batch-resource.pages.list-batches';

    // Email Modal State
    public bool $showEmailModal = false;
    public ?int $selectedBatchId = null;
    public string $emailMode = 'bulk'; // 'bulk' or 'single'
    public array $selectedInternIds = [];
    public ?int $singleInternId = null;
    public string $customSubject = 'Techstrota Internship — Batch Allocation & Schedule Details';
    public string $customNote = '';

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function openEmailModal(int $batchId): void
    {
        $batch = InternshipBatch::with(['interns' => fn ($q) => $q->where('is_active', true)])->find($batchId);
        if (!$batch) {
            return;
        }

        $this->selectedBatchId = $batchId;
        $this->emailMode = 'bulk';
        
        // Auto-select all enrolled active interns who have an email
        $activeWithEmail = $batch->interns->where('is_active', true)->whereNotNull('email')->where('email', '!=', '');
        $this->selectedInternIds = $activeWithEmail->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->singleInternId = $activeWithEmail->first()?->id;

        $this->customSubject = "Techstrota Internship — Batch Allocation & Schedule Details ({$batch->batch_name})";
        $this->customNote = '';
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->selectedBatchId = null;
        $this->selectedInternIds = [];
        $this->singleInternId = null;
        $this->customNote = '';
    }

    public function toggleSelectAll(): void
    {
        if (!$this->selectedBatchId) return;

        $batch = InternshipBatch::with(['interns' => fn ($q) => $q->where('is_active', true)])->find($this->selectedBatchId);
        $allIds = $batch?->interns->whereNotNull('email')->where('email', '!=', '')->pluck('id')->map(fn ($id) => (int) $id)->toArray() ?? [];

        if (count($this->selectedInternIds) === count($allIds)) {
            $this->selectedInternIds = [];
        } else {
            $this->selectedInternIds = $allIds;
        }
    }

    public function sendBatchEmail(): void
    {
        if (!$this->selectedBatchId) {
            return;
        }

        $batch = InternshipBatch::with(['interns.offerletter', 'interns.team'])->find($this->selectedBatchId);
        if (!$batch) {
            \Filament\Notifications\Notification::make()->title('Batch not found')->danger()->send();
            return;
        }

        $targetInternIds = [];
        if ($this->emailMode === 'bulk') {
            $targetInternIds = $this->selectedInternIds;
        } else {
            if ($this->singleInternId) {
                $targetInternIds = [(int) $this->singleInternId];
            }
        }

        if (empty($targetInternIds)) {
            \Filament\Notifications\Notification::make()
                ->title('No Recipients Selected')
                ->body('Please select at least one intern to send the email.')
                ->warning()
                ->send();
            return;
        }

        $interns = \App\Models\InternManagement\Intern::whereIn('id', $targetInternIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($interns->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('No Valid Email Addresses')
                ->body('Selected intern(s) do not have a valid email address.')
                ->warning()
                ->send();
            return;
        }

        $sentCount = 0;
        $failedNames = [];

        foreach ($interns as $intern) {
            try {
                \Illuminate\Support\Facades\Mail::to($intern->email)->send(
                    new \App\Mail\InternBatchAllocationMail(
                        intern: $intern,
                        batch: $batch,
                        customNote: filled(trim($this->customNote)) ? trim($this->customNote) : null,
                        customSubject: filled(trim($this->customSubject)) ? trim($this->customSubject) : null
                    )
                );
                $sentCount++;
            } catch (\Throwable $e) {
                $failedNames[] = $intern->name;
            }
        }

        $this->closeEmailModal();

        if ($sentCount > 0) {
            $msg = $this->emailMode === 'single'
                ? "Batch allocation email successfully sent to {$interns->first()->name} ({$interns->first()->email})."
                : "Batch allocation email sent to {$sentCount} intern(s) of batch '{$batch->batch_name}'.";

            \Filament\Notifications\Notification::make()
                ->title('Batch Email Sent Successfully')
                ->body($msg)
                ->success()
                ->send();
        }

        if (!empty($failedNames)) {
            \Filament\Notifications\Notification::make()
                ->title('Email Failed for Some Interns')
                ->body('Failed to send to: ' . implode(', ', $failedNames))
                ->danger()
                ->send();
        }
    }

    public function mount(): void
    {
        parent::mount();

        // Auto-archive any active batches whose assigned interns have all completed/promoted
        $batches = InternshipBatch::where('is_archived', false)->get();
        foreach ($batches as $batch) {
            $batch->checkAndAutoArchive();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Batch')
                ->icon('heroicon-m-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InternshipBatchResource\Widgets\InternshipBatchStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Batches')
                ->icon('heroicon-m-check-circle')
                ->badge(InternshipBatch::query()->where('is_archived', false)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_archived', false)),

            'archived' => Tab::make('Archived Batches')
                ->icon('heroicon-m-archive-box')
                ->badge(InternshipBatch::query()->where('is_archived', true)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_archived', true)),

            'all' => Tab::make('All Batches')
                ->icon('heroicon-m-rectangle-stack')
                ->badge(InternshipBatch::count()),
        ];
    }
}
