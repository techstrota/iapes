<?php

namespace App\Filament\Resources\InternManagement\InternTeamResource\Pages;

use App\Filament\Resources\InternManagement\InternTeamResource;
use App\Models\InternManagement\InternTeam;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInternTeams extends ListRecords
{
    protected static string $resource = InternTeamResource::class;
    
    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ New Project Team')
                ->color('warning'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Projects')
                ->icon('heroicon-m-bolt')
                ->badge(InternTeam::query()->where('is_archived', false)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_archived', false)),

            'archived' => Tab::make('Archived Projects')
                ->icon('heroicon-m-archive-box')
                ->badge(InternTeam::query()->where('is_archived', true)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_archived', true)),

            'all' => Tab::make('All Projects')
                ->icon('heroicon-m-squares-plus')
                ->badge(InternTeam::count()),
        ];
    }
}
