<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;

use App\Filament\Resources\CandidateManagement\OfferLetterResource;
use App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets\OfferLetterStatsOverview;
use App\Models\InterviewManagement\OfferLetter;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOfferLetters extends ListRecords
{
    protected static string $resource = OfferLetterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Offer Letter'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OfferLetterStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        $draftCount    = OfferLetter::where('offer_status', 'draft')->count();
        $acceptedCount = OfferLetter::where('offer_status', 'accepted')->count();
        $rejectedCount = OfferLetter::where('offer_status', 'rejected')->count();
        $allCount      = OfferLetter::count();

        return [
            'drafts' => Tab::make('🟡 Drafts')
                ->badge($draftCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'draft')),

            'accepted' => Tab::make('✅ Accepted')
                ->badge($acceptedCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'accepted')),

            'rejected' => Tab::make('❌ Rejected')
                ->badge($rejectedCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'rejected')),

            'all' => Tab::make('All')
                ->badge($allCount)
                ->badgeColor('gray'),
        ];
    }
}
