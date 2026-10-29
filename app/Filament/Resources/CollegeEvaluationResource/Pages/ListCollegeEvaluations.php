<?php

namespace App\Filament\Resources\CollegeEvaluationResource\Pages;

use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluation;
use App\Models\ReportManagement\CollegeEvaluationIntern;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListCollegeEvaluations extends Page
{
    use WithPagination;

    protected static string $resource = CollegeEvaluationResource::class;

    protected static string $view = 'filament.college-evaluation.list-college-evaluations';

    protected static ?string $title = 'College & University Evaluations';

    public string $search = '';
    public string $collegeFilter = 'all';
    public string $durationFilter = 'all';
    public int $perPage = 6;

    // Delete Modal State
    public bool $showDeleteModal = false;
    public ?int $batchIdToDelete = null;
    public ?string $batchTitleToDelete = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCollegeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDurationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function downloadPdf(int $id)
    {
        $evaluation = CollegeEvaluation::find($id);
        if (!$evaluation) {
            Notification::make()->title('Evaluation not found')->danger()->send();
            return null;
        }

        return CollegeEvaluationResource::downloadPdf($evaluation);
    }

    public function confirmDelete(int $id, string $title): void
    {
        $this->batchIdToDelete = $id;
        $this->batchTitleToDelete = $title;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->batchIdToDelete = null;
        $this->batchTitleToDelete = null;
    }

    public function executeDelete(): void
    {
        if (!$this->batchIdToDelete) {
            return;
        }

        $evaluation = CollegeEvaluation::find($this->batchIdToDelete);
        if ($evaluation) {
            $title = $evaluation->batch_title ?: 'Evaluation Batch';
            $evaluation->delete();

            Notification::make()
                ->title('Evaluation Removed')
                ->body("'{$title}' has been deleted successfully.")
                ->success()
                ->send();
        }

        $this->closeDeleteModal();
        $this->resetPage();
    }

    public function getStats(): array
    {
        $totalEvaluations = CollegeEvaluation::count();
        $totalInternsEvaluated = CollegeEvaluationIntern::count();
        $uniqueColleges = CollegeEvaluation::distinct('college_name')->count('college_name');
        $avgBatchSize = $totalEvaluations > 0 ? round($totalInternsEvaluated / $totalEvaluations, 1) : 0;

        return [
            'total_evaluations'       => $totalEvaluations,
            'total_interns_evaluated' => $totalInternsEvaluated,
            'unique_colleges'         => $uniqueColleges,
            'avg_batch_size'          => $avgBatchSize,
        ];
    }

    public function getEvaluationsQuery()
    {
        $query = CollegeEvaluation::with(['college', 'evaluationInterns']);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('batch_title', 'like', $term)
                  ->orWhere('college_name', 'like', $term)
                  ->orWhere('university_name', 'like', $term)
                  ->orWhere('course_degree', 'like', $term)
                  ->orWhere('academic_year', 'like', $term)
                  ->orWhereHas('college', fn ($c) => $c->where('name', 'like', $term)->orWhere('university_name', 'like', $term));
            });
        }

        if ($this->collegeFilter !== 'all') {
            $query->where(function ($q) {
                $q->where('college_name', $this->collegeFilter)
                  ->orWhereHas('college', fn ($c) => $c->where('name', $this->collegeFilter));
            });
        }

        if ($this->durationFilter !== 'all') {
            $query->where('duration', $this->durationFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getViewData(): array
    {
        $evaluations = $this->getEvaluationsQuery()->paginate($this->perPage);
        $stats = $this->getStats();

        $colleges = CollegeEvaluation::whereNotNull('college_name')
            ->where('college_name', '!=', '')
            ->distinct()
            ->pluck('college_name')
            ->toArray();

        $durations = CollegeEvaluation::whereNotNull('duration')
            ->where('duration', '!=', '')
            ->distinct()
            ->pluck('duration')
            ->toArray();

        return [
            'evaluations' => $evaluations,
            'stats'       => $stats,
            'colleges'    => $colleges,
            'durations'   => $durations,
        ];
    }
}
