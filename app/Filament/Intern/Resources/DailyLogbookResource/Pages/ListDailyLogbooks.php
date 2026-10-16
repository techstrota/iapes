<?php

namespace App\Filament\Intern\Resources\DailyLogbookResource\Pages;

use App\Filament\Intern\Resources\DailyLogbookResource;
use App\Models\InternManagement\DailyLogbook;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\QueryException;

class ListDailyLogbooks extends ListRecords
{
    protected static string $resource = DailyLogbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Daily Log')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Add Daily Work Log')
                ->modalDescription('Record what you have accomplished today.')
                ->using(function (array $data, Actions\CreateAction $action): ?DailyLogbook {
                    $internId = auth()->id();
                    $targetDate = Carbon::parse($data['date'])->format('Y-m-d');

                    // Check if entry already exists for this date
                    $existing = DailyLogbook::where('intern_id', $internId)
                        ->whereDate('date', $targetDate)
                        ->first();

                    if ($existing) {
                        Notification::make()
                            ->title('Logbook Entry Already Exists')
                            ->body('A logbook entry for ' . Carbon::parse($targetDate)->format('d M Y') . ' already exists. Please update that entry instead.')
                            ->danger()
                            ->persistent()
                            ->send();

                        $action->halt();
                        return null;
                    }

                    try {
                        return DailyLogbook::create([
                            'intern_id' => $internId,
                            'date' => $targetDate,
                            'tasks_completed' => $data['tasks_completed'],
                            'status' => 'submitted',
                        ]);
                    } catch (UniqueConstraintViolationException | QueryException $e) {
                        Notification::make()
                            ->title('Logbook Entry Already Exists')
                            ->body('A logbook entry for ' . Carbon::parse($targetDate)->format('d M Y') . ' already exists. Please update that entry instead.')
                            ->danger()
                            ->persistent()
                            ->send();

                        $action->halt();
                        return null;
                    }
                }),
        ];
    }
}
