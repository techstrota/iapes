<?php

namespace App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource\Pages;

use App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAssignedTasks extends ListRecords
{
    protected static string $resource = AssignedTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for interns
        ];
    }

    // ── Status Tabs ────────────────────────────────────────────────
    public function getTabs(): array
    {
        $userId = Auth::id();

        // Base scoped IDs: task_ids that belong to this intern
        // (replicates the getEloquentQuery scoping)
        $scopedTaskIds = TaskAssignment::query()
            ->where(function (Builder $q) use ($userId) {
                $q->where('intern_id', $userId)
                  ->orWhereHas('team.interns', fn ($q2) => $q2->where('interns.id', $userId))
                  ->orWhereExists(function ($q3) use ($userId) {
                      $q3->selectRaw(1)->from('interns')
                         ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                         ->where('interns.id', $userId);
                  });
            })
            ->pluck('task_id');

        $submittedIds  = TaskSubmission::where('intern_id', $userId)->pluck('task_id');
        $approvedIds   = TaskSubmission::where('intern_id', $userId)->where('status', 'approved')->pluck('task_id');
        $rejectedIds   = TaskSubmission::where('intern_id', $userId)->where('status', 'rejected')->pluck('task_id');
        $reviewedIds   = TaskSubmission::where('intern_id', $userId)->whereIn('status', ['submitted', 'reviewed'])->pluck('task_id');
        $pendingIds    = $scopedTaskIds->diff($submittedIds);

        return [
            
            'pending' => Tab::make('⏳ Pending')
                ->icon('heroicon-o-clock')
                ->badge($pendingIds->count())
                ->badgeColor($pendingIds->count() > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $pendingIds)),

            'submitted' => Tab::make('📬 Submitted')
                ->icon('heroicon-o-paper-airplane')
                ->badge($reviewedIds->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $reviewedIds)),

            'approved' => Tab::make('✅ Approved')
                ->icon('heroicon-o-check-circle')
                ->badge($approvedIds->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $approvedIds)),

            'rejected' => Tab::make('❌ Rejected')
                ->icon('heroicon-o-x-circle')
                ->badge($rejectedIds->count())
                ->badgeColor($rejectedIds->count() > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $rejectedIds)),

            'all' => Tab::make('All')
                ->icon('heroicon-o-squares-2x2')
                ->badge($scopedTaskIds->count()),
        ];
    }
}
