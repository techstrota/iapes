<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;

use App\Models\InterviewManagement\Application;

// 1. IMPORT THE TRAIT
use App\Traits\HasInterviewActions;


class LatestApplications extends BaseWidget
{
    // 2. TELL THE CLASS TO USE THE TRAIT
    use HasInterviewActions;

    protected static ?string $heading = null;

    protected int | string | array $columnSpan = 'full'; // optional (makes it full width)

    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ...
                Application::query()
                    ->where('status', 'applied') // adjust if your column name is different
                    ->whereNotNull('name')   // ✅ name must not be NULL
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('domain')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Applied On')
                    ->date()
                    ->sortable(),
            ])

            ->actions([

                // Individual row button
            static::getScheduleInterviewAction(),
            
            ]);
    }
}
