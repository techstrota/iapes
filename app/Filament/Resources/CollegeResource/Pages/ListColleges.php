<?php

namespace App\Filament\Resources\CollegeResource\Pages;

use App\Filament\Resources\CollegeResource;
use App\Models\ReportManagement\College;
use App\Models\ReportManagement\CollegeEvaluation;
use App\Models\InternManagement\Intern;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListColleges extends Page
{
    use WithPagination;

    protected static string $resource = CollegeResource::class;

    protected static string $view = 'filament.college.list-colleges';

    protected static ?string $title = 'Colleges';

    public string $search = '';
    public string $universityFilter = 'all';
    public int $perPage = 6;

    // Delete Modal State
    public bool $showDeleteModal = false;
    public ?int $collegeIdToDelete = null;
    public ?string $collegeNameToDelete = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUniversityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $collegeId, string $name): void
    {
        $this->collegeIdToDelete = $collegeId;
        $this->collegeNameToDelete = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->collegeIdToDelete = null;
        $this->collegeNameToDelete = null;
    }

    public function executeDelete(): void
    {
        if (!$this->collegeIdToDelete) {
            return;
        }

        $college = College::find($this->collegeIdToDelete);
        if ($college) {
            $name = $college->name;
            $college->delete();

            Notification::make()
                ->title('College Removed')
                ->body("'{$name}' has been deleted successfully.")
                ->success()
                ->send();
        }

        $this->closeDeleteModal();
        $this->resetPage();
    }

    public function getStats(): array
    {
        $totalColleges = College::count();
        $totalBatches  = CollegeEvaluation::active()->count();
        $archivedBatches = CollegeEvaluation::archived()->count();
        $uniqueUniversities = College::whereNotNull('university_name')
            ->where('university_name', '!=', '')
            ->distinct('university_name')
            ->count('university_name');

        $activeInterns = Intern::active()
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->count();

        return [
            'total_colleges'      => $totalColleges,
            'total_batches'       => $totalBatches,
            'archived_batches'    => $archivedBatches,
            'unique_universities' => $uniqueUniversities,
            'active_interns'      => $activeInterns,
        ];
    }

    public function getCollegesQuery()
    {
        $query = College::withCount(['evaluations', 'activeEvaluations', 'archivedEvaluations']);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('university_name', 'like', $term)
                  ->orWhere('address', 'like', $term);
            });
        }

        if ($this->universityFilter !== 'all') {
            if ($this->universityFilter === 'autonomous') {
                $query->where(function ($q) {
                    $q->whereNull('university_name')->orWhere('university_name', '');
                });
            } else {
                $query->where('university_name', $this->universityFilter);
            }
        }

        return $query->orderBy('name', 'asc');
    }

    public function getViewData(): array
    {
        $colleges = $this->getCollegesQuery()->paginate($this->perPage);
        $stats = $this->getStats();
        $universities = College::whereNotNull('university_name')
            ->where('university_name', '!=', '')
            ->distinct()
            ->pluck('university_name')
            ->toArray();

        return [
            'colleges'            => $colleges,
            'stats'               => $stats,
            'universities'        => $universities,
            'showDeleteModal'     => $this->showDeleteModal,
            'collegeIdToDelete'   => $this->collegeIdToDelete,
            'collegeNameToDelete' => $this->collegeNameToDelete,
        ];
    }
}
