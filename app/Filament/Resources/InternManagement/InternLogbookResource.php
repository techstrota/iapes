<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\InternLogbookResource\Pages;
use App\Models\InternManagement\DailyLogbook;
use App\Models\InternManagement\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{DatePicker, Textarea, Select};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class InternLogbookResource extends Resource
{
    protected static ?string $model = DailyLogbook::class;
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Intern Logbooks';
    protected static ?string $modelLabel = 'Intern Logbook';
    protected static ?string $pluralModelLabel = 'Intern Logbooks';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('intern_id')
                    ->label('Intern')
                    ->options(fn () => Intern::where('is_active', true)->get()->mapWithKeys(fn ($i) => [
                        $i->id => "{$i->name} (" . ($i->intern_code ?: 'INT-' . str_pad($i->id, 3, '0', STR_PAD_LEFT)) . ")"
                    ]))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                DatePicker::make('date')
                    ->label('Log Date')
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->displayFormat('D, d M Y')
                    ->rules([
                        static function (Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record): \Closure {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $internId = $get('intern_id');
                                if (!$internId) return;

                                $targetDate = \Carbon\Carbon::parse($value)->format('Y-m-d');
                                $query = DailyLogbook::where('intern_id', $internId)
                                    ->whereDate('date', $targetDate);

                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }

                                if ($query->exists()) {
                                    $fail('A logbook entry for ' . \Carbon\Carbon::parse($targetDate)->format('d M Y') . ' already exists for this intern. Please update that entry instead.');
                                }
                            };
                        },
                    ])
                    ->columnSpanFull(),

                Textarea::make('tasks_completed')
                    ->label("Today's Work Note")
                    ->placeholder("Intern's work description, tasks completed, or progress made...")
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('supervisor_remarks')
                    ->label('Supervisor Remarks / Feedback (Optional)')
                    ->placeholder('Enter any mentor feedback, review notes, or guidance...')
                    ->rows(3)
                    ->columnSpanFull(),
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

                TextColumn::make('intern.name')
                    ->label('Intern')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (DailyLogbook $record) => $record->intern?->intern_code ?: ('INT-' . str_pad($record->intern_id, 3, '0', STR_PAD_LEFT))),

                TextColumn::make('intern.batch.batch_name')
                    ->label('Batch')
                    ->badge()
                    ->color('info')
                    ->placeholder('Unassigned')
                    ->toggleable(),

                TextColumn::make('tasks_completed')
                    ->label("Work Note")
                    ->limit(80)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('supervisor_remarks')
                    ->label('Supervisor Remarks')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime('d M, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('intern_id')
                    ->label('Intern')
                    ->options(fn () => Intern::where('is_active', true)->pluck('name', 'id'))
                    ->searchable(),

                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $d) => $q->whereDate('date', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('date', '<=', $d));
                    }),
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
            'index' => Pages\ListInternLogbooks::route('/'),
        ];
    }
}
