<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollegeResource\Pages;
use App\Models\ReportManagement\College;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Section, Grid};
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn};

class CollegeResource extends Resource
{
    protected static ?string $model = College::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Marks Evaluation';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('College Information')
                ->description('Specify college details.')
                ->schema([
                    TextInput::make('name')
                        ->label('College / Institution Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Silver Oak College of Engineering & Technology')
                        ->columnSpanFull(),

                    TextInput::make('university_name')
                        ->label('Affiliated University (Optional)')
                        ->nullable()
                        ->maxLength(255)
                        ->placeholder('e.g. Gujarat Technological University (GTU) — Leave blank if Autonomous/Direct')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('address')
                        ->label('Address (Optional)')
                        ->nullable()
                        ->rows(3)
                        ->placeholder('e.g. Gota, Ahmedabad, Gujarat 382481')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('College Profile')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->label('College / Institution')
                                ->weight('bold')
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                            Infolists\Components\TextEntry::make('university_name')
                                ->label('Affiliated University')
                                ->placeholder('Autonomous / Direct Campus'),

                            Infolists\Components\TextEntry::make('address')
                                ->label('Address')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('College / Institution Name')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('university_name')
                    ->label('Affiliated University')
                    ->searchable()
                    ->badge()
                    ->color('purple')
                    ->placeholder('Autonomous Campus'),

                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('evaluations_count')
                    ->counts('evaluations')
                    ->label('Batches')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\Action::make('create_batch')
                    ->label('New Batch')
                    ->icon('heroicon-m-plus-circle')
                    ->color('primary')
                    ->url(fn (College $record) => CollegeEvaluationResource::getUrl('create', ['college_id' => $record->id])),

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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount('evaluations');
    }

    public static function getRelations(): array
    {
        return [
            CollegeResource\RelationManagers\EvaluationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListColleges::route('/'),
            'create' => Pages\CreateCollege::route('/create'),
            'view'   => Pages\ViewCollege::route('/{record}'),
            'edit'   => Pages\EditCollege::route('/{record}/edit'),
        ];
    }
}
