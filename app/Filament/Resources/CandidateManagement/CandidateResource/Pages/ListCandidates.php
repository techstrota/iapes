<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Pages;

use App\Filament\Resources\CandidateManagement\CandidateResource;
use App\Filament\Resources\CandidateManagement\CandidateResource\Widgets\CandidateStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CandidateStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create action — candidates come from public form
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'applied';
    }

    public function getTabs(): array
    {
        $batchDateSub = \App\Models\InterviewManagement\InterviewBatch::select('interview_date')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        $batchTimeSub = \App\Models\InterviewManagement\InterviewBatch::select('start_time')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        $batchNameSub = \App\Models\InterviewManagement\InterviewBatch::select('interview_batch_name')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        return [
            'applied' => Tab::make('Applied')
                ->icon('heroicon-o-inbox')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'applied')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::where('status', 'applied')->count())
                ->badgeColor('gray'),

            'interview_scheduled' => Tab::make('Scheduled')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'interview_scheduled')
                    ->reorder()
                    ->orderByRaw('(' . $batchDateSub->toSql() . ') IS NULL ASC')
                    ->addBinding($batchDateSub->getBindings(), 'order')
                    ->orderBy($batchDateSub, 'asc')
                    ->orderBy($batchTimeSub, 'asc')
                    ->orderBy($batchNameSub, 'asc')
                    ->orderBy('application_code', 'asc')
                )
                ->badge(fn () => \App\Models\InterviewManagement\Application::where('status', 'interview_scheduled')->count())
                ->badgeColor('info'),

            'interviewed' => Tab::make('Interviewed')
                ->icon('heroicon-o-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'interviewed')
                    ->reorder()
                    ->orderByRaw('(' . $batchDateSub->toSql() . ') IS NULL ASC')
                    ->addBinding($batchDateSub->getBindings(), 'order')
                    ->orderBy($batchDateSub, 'desc')
                    ->orderBy($batchTimeSub, 'desc')
                    ->orderBy($batchNameSub, 'asc')
                    ->orderBy('application_code', 'asc')
                )
                ->badge(fn () => \App\Models\InterviewManagement\Application::where('status', 'interviewed')->count())
                ->badgeColor('warning'),

            'shortlisted' => Tab::make('Shortlisted')
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'shortlisted')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::where('status', 'shortlisted')->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Rejected')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::where('status', 'rejected')->count())
                ->badgeColor('danger'),

            'all' => Tab::make('All Candidates')
                ->icon('heroicon-o-users')
                ->modifyQueryUsing(fn (Builder $query) => $query->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::whereNotIn('status', ['pending', 'verified'])->count()),
        ];
    }
}
