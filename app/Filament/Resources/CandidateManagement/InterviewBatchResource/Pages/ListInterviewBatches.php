<?php

namespace App\Filament\Resources\CandidateManagement\InterviewBatchResource\Pages;

use App\Filament\Resources\CandidateManagement\InterviewBatchResource;
use App\Filament\Resources\CandidateManagement\InterviewBatchResource\Widgets\InterviewBatchStatsOverview;
use App\Models\InterviewManagement\InterviewBatch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInterviewBatches extends ListRecords
{
    protected static string $resource = InterviewBatchResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InterviewBatchStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Interview Batch')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'upcoming';
    }

    public function getTabs(): array
    {
        return [
            
            'upcoming' => Tab::make('Upcoming & Scheduled')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('workflow_status', 'scheduled'))
                ->badge(fn () => InterviewBatch::where('workflow_status', 'scheduled')->count())
                ->badgeColor('info'),

            'open' => Tab::make('Open Slots')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('capacity_status', 'open'))
                ->badge(fn () => InterviewBatch::where('capacity_status', 'open')->count())
                ->badgeColor('success'),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-o-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('workflow_status', 'completed'))
                ->badge(fn () => InterviewBatch::where('workflow_status', 'completed')->count())
                ->badgeColor('gray'),

            'all' => Tab::make('All Batches')
                ->icon('heroicon-o-squares-2x2')
                ->badge(fn () => InterviewBatch::count()),

        ];
    }
}
