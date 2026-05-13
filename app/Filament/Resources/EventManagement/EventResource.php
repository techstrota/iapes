<?php

namespace App\Filament\Resources\EventManagement;

use App\Filament\Resources\EventManagement\EventResource\Pages;
use App\Filament\Resources\EventManagement\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Select, DatePicker, FileUpload, Textarea, RichEditor};
use Filament\Forms\Get; // <--- MAKE SURE THIS IS PRESENT
use Filament\Forms\Set; // (Optional, if you use Set)
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, };
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

use Filament\Tables\Actions\{Action, ActionGroup, BulkAction};



class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('event_title')->required(),

                Textarea::make('event_description')
                    ->required(),
                    
                TextInput::make('event_type')
                    ->label('Event Type')
                    ->datalist([
                        'Seminar',
                        'Hackathon',
                        'Workshop',
                        'Conference',
                        'Meetup'
                    ])
                    ->autocomplete(false) // Prevents browser autofill from overlapping suggestions
                    ->required(),

                Select::make('is_multiday')
                    ->label('Is Multi-Day Event')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No'
                    ])
                    ->default('no')
                    ->live(),
                  //  ->required(),

                DatePicker::make('event_start_date')
                    ->label(fn (Get $get): string => $get('is_multiday') === 'yes' ? 'Event Start Date' : 'Event Date')
                    ->required(),

                DatePicker::make('event_end_date')
                    ->required()
                    ->different('event_start_date')
                    ->visible(fn (Get $get): bool => $get('is_multiday') === 'yes')
                    ->label('Event End Date'),

                Select::make('type')
                    ->label('Is this Event Online or Offline ?')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->live() // This is crucial for real-time reactivity
                    ->required(),

                TextInput::make('meeting_platform')
                    ->label('Meeting Platform')
                    ->visible(fn (Get $get) => $get('type') === 'online')
                    ->placeholder('Google Meet , Zoom etc')
                    ->required(),

                TextInput::make('meeting_link')
                    ->url()
                    ->label('Meeting Link')
                    ->placeholder('https://meet.google.com/')
                    ->visible(fn (Get $get) => $get('type') === 'online'),

                TextInput::make('event_venue')
                    ->label('Event Location')
                    ->placeholder('123 Business St, New York')
                    ->required()
                    ->visible(fn (Get $get): bool => $get('type') === 'offline'),

                Select::make('event_status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'completed' => 'Completed',
                    ])
                    ->default('upcoming')
                    ->required(),
                
                RichEditor::make('skills')
                    ->label('Skills will be Gain By Participant'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc') 
            ->columns([
                //
                TextColumn::make('event_title')
                    ->label('Event Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => Str::limit($record->event_description, 30))
                    ->tooltip(fn ($record): string => $record->event_description),

                TextColumn::make('event_type')
                    ->label('Event Type')  
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),

                TextColumn::make('event_start_date')
                    ->label('Event Date(s)')
                    ->formatStateUsing(function ($record, $state) {
                        $start = \Carbon\Carbon::parse($state)->format('M j, Y');
                        if ($record->event_end_date) {
                            $end = \Carbon\Carbon::parse($record->event_end_date)->format('M j, Y');
                            return "{$start} - {$end}";
                        }
                        return $start;
                    })
                    ->description(fn ($record): string => $record->event_end_date ? 'Multi-day Event' : 'Single Day Event')
                    ->sortable(),

                TextColumn::make('event_venue')
                    ->label('Location / Link')
                    ->state(function ($record): string {   
                        return $record->type === 'online'  // Logic to show either the physical location or the online link
                            ? ($record->meeting_link ?? 'No Link Provided') 
                            : ($record->event_venue ?? 'No Location');
                    })
                    ->tooltip(fn ($record): string => $record->type === 'online' // Dynamic Tooltip: Shows the full meeting link or full venue address on hover
                        ? ($record->meeting_link ?? 'No Link Provided') 
                        : ($record->event_venue ?? 'No Location'))
                    ->icon(fn ($record): string => $record->type === 'online' ? 'heroicon-m-computer-desktop' : 'heroicon-m-map-pin')
                    ->description(fn ($record): string => $record->type === 'online' ? 'Online Event' : 'Physical Venue')
                    ->limit(30)
                    ->copyable(fn ($record): bool => $record->type === 'online' ? filled($record->meeting_link) : filled($record->event_venue)) // Adds a click-to-copy feature
                    ->copyMessage(fn ($record): string => $record->meeting_link ? 'Link Copied' : 'Location Copied!'),
                
                TextColumn::make('event_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'upcoming' => 'warning',
                    })
                    ->formatStateUsing(function ($record, $state) {
                    // If the date is before today and status isn't 'completed'
                    if ($record->event_start_date < now()->toDateString() && $state !== 'completed') {
                        $record->update(['event_status' => 'completed']);
                        return 'Completed';
                    }
                    return ucfirst($state);
                }),

                TextColumn::make('registrations_count')
                    ->counts('registrations') // Must match the method name in your Event Model
                    ->label('No. of Registrations')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                ]),
            ])
            
            ->actions([
                    Action::make('view_registrations')
                        ->label('View Registrations')
                        ->icon('heroicon-o-users')
                        ->url(fn (Event $record) => EventRegistrationResource::getUrl('index', [
                            'tableFilters[event][value]' => $record->id,
                        ])),
                   // Tables\Actions\EditAction::make(),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }



    
}
  
