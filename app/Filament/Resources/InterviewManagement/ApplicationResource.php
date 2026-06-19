<?php

namespace App\Filament\Resources\InterviewManagement;

use App\Filament\Resources\InterviewManagement\ApplicationResource\Pages;
use App\Filament\Resources\InterviewManagement\ApplicationResource\RelationManagers;
use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\OfferLetter;
use App\Mail\InterviewScheduledMail;
use Carbon\Carbon;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Textarea, FileUpload, Select, DatePicker, TimePicker, Section, Grid};
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, BulkAction};
use Filament\Tables\Columns\{TextColumn, BadgeColumn, IconColumn, SelectColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

// 1. IMPORT THE TRAIT
use App\Traits\HasInterviewActions;
use App\Models\InternManagement\Intern;
use Illuminate\Support\Facades\Hash;

class ApplicationResource extends Resource
{
    // 2. TELL THE CLASS TO USE THE TRAIT
    use HasInterviewActions;
    
    protected static ?string $model = Application::class;
    protected static ?string $navigationGroup = 'Interview Management';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Intern Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('application_code')
                                ->label('Application Code')
                                ->placeholder('Auto-generated on save')
                                ->disabled() // User can't edit it
                                ->dehydrated(false),   
                                
                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(255)
                                ->regex('/^(?=(?:.*?\s){1,5}(?![^\s]*\s))[a-zA-Z\s]+$/') // Validation: Only alphabets and spaces, with exactly 3 to 5 spaces total
                                ->validationMessages([
                                    'regex' => 'The name must only contain letters and 3 to 5 white spaces.',
                                ]),

                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->required()
                                ->maxLength(15),
                            TextInput::make('college')
                                ->label('College Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('degree')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('year')
                                ->label('Year')
                                ->placeholder('e.g. Sem 3, Sem 6')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('cgpa')
                                ->label('CGPA / Percentage')
                                ->numeric()       // Ensures only numbers are entered
                                ->step(0.01)  // Allows decimals like 8.55
                                ->required()    
                                ->maxValue(100),  // Optional: prevents unrealistic numbers

                            TextInput::make('domain')
                                ->label('Interested Internship Field')
                                ->required()
                                //->searchable()
                            //  ->disabled(fn ($record) => $record?->status !== 'applied'),
                        ]),

                    ]),


                     Section::make('duration')
                                ->schema([
                                   TextInput::make('duration')
                                    ->label('Internship Duration')
                                    ->numeric()
                                    ->required(),

                                    Select::make('duration_unit')
                                    ->label('Internship Duration')
                            ->options([
                                'months' => 'Months',
                                'days' => 'Days',
                                'hours' => 'Hours',
                               
                            ])
                            ->required(),
                                      
                                ])->columns(2),

                            Section::make('Skills')
                                ->schema([
                                    Textarea::make('skills')
                                        ->label('Skills (comma separated)')
                                        ->rows(3)
                                        ->required(),
                                ]),

                            Section::make('Resume')
                                ->schema([
                                    FileUpload::make('resume_path')
                                        ->label('Resume')
                                        ->disk('public')
                                        ->directory('resumes')
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                        ])
                                        // ->required()
                                        ->downloadable()
                                        ->openable()
                                        ->preserveFilenames(),
                                ])->columns(3),

                            // Select::make('status')
                            // ->options([
                            //     'Applied' => 'Applied',
                            //     'Interview_Scheduled' => 'Interview Scheduled',
                            //     'Interviewed' => 'Interviewed',
                            //     'Shortlisted' => 'Shortlisted',
                            //     'Rejected' => 'Rejected',
                            // ])
                            // ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordClasses(fn ($record) => $record->status === 'interview_scheduled' ? 'bg-green-50 border-l-4 border-green-500' : null)
            ->poll('3s') // ⬅ auto refresh
            ->defaultSort('created_at', 'desc') 
            ->columns([
                TextColumn::make('application_code')
                    ->searchable()
                    ->weight('bold')
                    ->icon(fn ($record) => match ($record->status) {
                        'shortlisted' => 'heroicon-m-star',
                        'interview_scheduled' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->iconColor(fn ($record) => match ($record->status) {
                        'shortlisted' => 'warning',          // yellow
                        'interview_scheduled' => 'success',  // green
                        'rejected' => 'danger',              // red
                        default => null,
                    })
                    ->color(fn ($record) => match ($record->status) {
                        'shortlisted' => 'warning',
                        'interview_scheduled' => 'success',
                        'rejected' => 'danger',
                        default => null,
                        }),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone') ->toggleable(),
                TextColumn::make('domain')
                    ->label('Internship Field')
                    ->badge()
                    ->toggleable()
                    ->color('info'),
                SelectColumn::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'interview_scheduled' => 'Interview Scheduled',
                        'interviewed' => 'Interviewed',
                        'shortlisted' => 'Shortlisted',
                        'rejected' => 'Rejected',
                    ])
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(function ($record, $state) {
                        // Check if status is changed to Shortlisted and no offer letter exists yet
                        if ($state === 'Shortlisted' && !$record->offerLetter) {
                            // Your logic to create the Offer Letter here
                            $record->offerLetter()->create([
                                'application_id' => $record->id,
                                'intern_name' => $record->name,
                                'university' => $record->college,
                                'college' => $record->college,
                                'joining_date' => $joiningDate,
                                'completion_date' => $completionDate,
                                'internship_role' => $record->domain,
                                'internship_position' => 'Intern',
                                'working_hours' => '40 hours per week',
                                'template' => 'general', // Default template
                            ]);

                            Notification::make()
                                ->title('Offer Letter Generated')
                                ->body("A new offer letter record has been created for {$record->name}.")
                                ->success()
                                ->send();
                        }
                    }),
                    // ->required()
                    // ->alignCenter()
                    // ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('college') 
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('degree') 
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('year')
                    ->label('Year/Semester')
                    ->toggleable(),
                TextColumn::make('cgpa')
                    ->label('CGPA')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('skills')  
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('resume_path')
                    ->label('Resume')
                    ->icon(fn ($state) => $state ? 'heroicon-o-eye' : 'heroicon-o-x-mark')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->url(fn ($record) => $record->resume_path 
                        ? asset('storage/' . $record->resume_path) 
                        : null
                    )
                    ->openUrlInNewTab(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
               Tables\Actions\DeleteBulkAction::make(),
              // 3. CALL THE TRAIT METHOD HERE
                    static::getScheduleInterviewBulkAction(),
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotIn('status', ['pending', 'verified']);
    }
}
