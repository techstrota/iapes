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

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
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
