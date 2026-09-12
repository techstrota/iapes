<?php

namespace App\Filament\Resources\TaskManagement;

use App\Filament\Resources\TaskManagement\TaskSubmissionAndEvaluationResource\Pages;
use App\Filament\Resources\TaskManagement\TaskSubmissionAndEvaluationResource\RelationManagers;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class TaskSubmissionAndEvaluationResource extends Resource
{
    protected static ?string $model = TaskSubmission::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = 'Task And Evaluation Management';
    protected static ?string $navigationLabel = 'Submission And Evaluation';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Section::make('Intern Submission')
                ->schema([
                    Forms\Components\Textarea::make('submission_text')
                        ->label('Submission Content')
                        ->disabled(),
                    Forms\Components\FileUpload::make('submission_file')
                        ->label('Submitted File')
                        ->directory('task-submissions')
                        ->openable() // Allows admin to view the file in a new tab
                        ->downloadable()
                        ->disabled(),
                ])->columns(1),
            
            Forms\Components\Section::make('Grading & Assessment')
            ->description('Assign marks and provide feedback based on the quality of work.')
            ->schema([
                Forms\Components\TextInput::make('marks')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('/ 100')
                    ->label('Score'),
            
        Forms\Components\Select::make('grade')
            ->options([
                'O' => 'Outstanding',
                'A+' => 'Excellent',
                'A' => 'Very Good',
                'B' => 'Good',
                'C' => 'Average',
                'F' => 'Fail',
            ])
            ->native(false),

        Forms\Components\Textarea::make('admin_feedback')
            ->label('Specific Feedback')
            ->columnSpanFull(),
    ])->columns(2)
        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s') // ⬅ auto refresh
            ->columns([
                //
                Tables\Columns\TextColumn::make('task.title') 
                ->label('Task Title')
                ->sortable()
                ->description(fn ($record) => $record->task->description),

                Tables\Columns\TextColumn::make('intern.name') // Assuming an 'intern' relationship exists
                ->label('Submitted By'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Submitted at')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'submitted',
                        'warning' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        
                    ]),

                
            ])
            ->filters([
                //
            ])
            ->actions([


                Tables\Actions\Action::make('evaluate')
                    ->label('Review Task')
                    ->label('Review & Evaluate')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('primary')
                    ->modalHeading('Evaluate Submission')
                    ->modalWidth('2xl') // Increased width to handle two sections better
                    ->mountUsing(fn (Forms\ComponentContainer $form, $record) => $form->fill([
                        'status' => $record->status,
                        'marks' => $record->marks,
                        'grade' => $record->grade,
                        'admin_feedback' => $record->admin_feedback,
            ]))
            ->form([
        Forms\Components\Grid::make(2) // This creates the side-by-side layout
        ->schema([
            // SECTION 1: LEFT SIDE - INTERN'S SUBMISSION (READ-ONLY)
            Forms\Components\Section::make('Intern Submission')
                ->collapsible()
                ->compact()
                ->schema([
                    Forms\Components\Placeholder::make('intern_name')
                        ->label('Submitted By')
                        ->content(fn ($record) => $record->intern?->name ?? 'Unknown'),
                    
                    Forms\Components\Textarea::make('submission_text')
                        ->label('Submission Content')
                        ->rows(6) // Increased rows to match the height of the right side
                        ->disabled(),

                    Forms\Components\FileUpload::make('submission_file')
                        ->label('Submitted File')
                        ->directory('task-submissions')
                        ->openable()
                        ->downloadable()
                        ->disabled(),
                ])->columnSpan(1),

            // SECTION 2: RIGHT SIDE - GRADING & ASSESSMENT (EDITABLE)
            Forms\Components\Section::make('Grading & Assessment')
                ->description('Assign status, marks, and grade based on the work quality.')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject / Revision Required',
                            'reviewed' => 'Mark as Reviewed',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('marks')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('/ 100')
                                ->label('Score'),

                            Forms\Components\Select::make('grade')
                                ->options([
                                    'O' => 'Outstanding',
                                    'A+' => 'Excellent',
                                    'A' => 'Very Good',
                                    'B' => 'Good',
                                    'C' => 'Average',
                                    'F' => 'Fail',
                                ])
                                ->native(false),
                        ]),

                    Forms\Components\Textarea::make('admin_feedback')
                        ->label('Evaluation Remarks')
                        ->placeholder('Explain why it was approved or what needs fixing...')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columnSpan(1),
        ])
            ])
            ->modalWidth('6xl') // Ensures the modal is wide enough for the two columns
            
        
        ->action(function ($record, array $data): void {
            $record->update([
                'status' => $data['status'],
                'marks' => $data['marks'],
                'grade' => $data['grade'],
                'admin_feedback' => $data['admin_feedback'],
                'evaluated_at' => now(),// Good for tracking admin response time
            ]);

            Notification::make()
            ->title('Task Evaluated')
            ->body("The submission from {$record->intern->name} has been " . ucfirst($data['status']) . ".")
            ->success()
            ->send();

            // Optional: Send a notification to the intern
            Notification::make()
                ->title('Task Evaluated')
                ->body("Your submission has been {$data['status']}.")
                ->success()
                ->sendToDatabase($record->intern);
        }),

                    
            
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTaskSubmissionAndEvaluations::route('/'),
            'create' => Pages\CreateTaskSubmissionAndEvaluation::route('/create'),
           // 'edit' => Pages\EditTaskSubmissionAndEvaluation::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Hides the "New Task Submission" button
    }
}
