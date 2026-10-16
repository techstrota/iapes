<?php

namespace App\Filament\Intern\Resources;

use App\Filament\Intern\Resources\DailyLogbookResource\Pages;
use App\Models\InternManagement\DailyLogbook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{DatePicker, Textarea, Placeholder, Section};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DailyLogbookResource extends Resource
{
    protected static ?string $model = DailyLogbook::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Daily Logbook';
    protected static ?string $navigationGroup = 'Work & Tasks';
    protected static ?int $navigationSort = 3;

    public static function getPluralLabel(): ?string
    {
        return 'Daily Work Logbooks';
    }

    public static function getModelLabel(): string
    {
        return 'Daily Log';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('intern_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Log Date')
                    ->default(now())
                    ->maxDate(now())
                    ->required()
                    ->native(false)
                    ->displayFormat('D, d M Y')
                    ->rules([
                        static function (Forms\Get $get, ?Model $record): \Closure {
                            return function (string $attribute, $value, \Closure $fail) use ($record) {
                                $internId = auth()->id();
                                $targetDate = \Carbon\Carbon::parse($value)->format('Y-m-d');

                                $check = DailyLogbook::isDateEligibleForLog($targetDate, $internId);
                                if (!($check['allowed'] ?? $check['eligible'] ?? true)) {
                                    $fail($check['reason']);
                                    return;
                                }

                                // Check uniqueness per intern and date with normalized Y-m-d
                                $query = DailyLogbook::where('intern_id', $internId)
                                    ->whereDate('date', $targetDate);

                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }

                                if ($query->exists()) {
                                    $fail('A logbook entry for ' . \Carbon\Carbon::parse($targetDate)->format('d M Y') . ' already exists. Please update that entry instead.');
                                }
                            };
                        },
                    ])
                    ->columnSpanFull(),

                Textarea::make('tasks_completed')
                    ->label("Today's Work Note")
                    ->placeholder("What have you done today? Describe the tasks, progress, features or bug fixes you worked on...")
                    ->rows(6)
                    ->required()
                    ->columnSpanFull(),

                Section::make('Supervisor Feedback')
                    ->visible(fn (?Model $record) => $record && filled($record->supervisor_remarks))
                    ->schema([
                        Placeholder::make('supervisor_remarks')
                            ->label('Mentor Remarks')
                            ->content(fn (?Model $record) => $record?->supervisor_remarks),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y, D')
                    ->sortable()
                    ->width('140px'),

                TextColumn::make('tasks_completed')
                    ->label("Today's Work Note")
                    ->limit(100)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('supervisor_remarks')
                    ->label('Mentor Feedback')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('d M, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyLogbooks::route('/'),
        ];
    }
}
