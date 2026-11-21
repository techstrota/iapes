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
use Illuminate\Support\Collection;

class TaskSubmissionAndEvaluationResource extends Resource
{
    protected static ?string $model = TaskSubmission::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?string $navigationLabel = 'Submission And Evaluation';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 7;

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
                        ->disk('public')
                        ->openable() // Allows admin to view the file in a new tab
                        ->downloadable()
                        ->disabled(),
                ])->columns(1),
            
            Forms\Components\Section::make('Evaluation & Assessment')
            ->description('Assign score and provide feedback based on the quality of work.')
            ->schema([
                Forms\Components\TextInput::make('marks')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('/ 100')
                    ->label('Score'),

                Forms\Components\Textarea::make('admin_feedback')
                    ->label('Specific Feedback')
                    ->columnSpanFull(),
            ])->columns(1)
        
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
                    ->label('Review & Evaluate')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('primary')
                    ->extraAttributes([
                        'style' => 'background-color: #1e40af !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; border-radius: 8px !important; font-weight: 600 !important;',
                    ])
                    ->modalHeading('Evaluate Submission')
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalWidth('6xl')
                    ->mountUsing(fn (Forms\ComponentContainer $form, $record) => $form->fill([
                        'status' => $record->status,
                        'marks' => $record->marks,
                        'admin_feedback' => $record->admin_feedback,
                    ]))
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // SECTION 1: LEFT SIDE - INTERN'S SUBMISSION (READ-ONLY)
                                Forms\Components\Section::make('Intern Deliverable Details')
                                    ->description('Review the submitted content and attachments.')
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
                        ->disk('public')
                        ->openable()
                        ->downloadable()
                        ->disabled(),
                ])->columnSpan(1),

            // SECTION 2: RIGHT SIDE - GRADING & ASSESSMENT (EDITABLE)
            Forms\Components\Section::make('Evaluation & Assessment')
                ->description('Assign status, score, and feedback based on the work quality.')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject / Revision Required',
                            'reviewed' => 'Mark as Reviewed',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('marks')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('/ 100')
                        ->label('Score'),

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

                Tables\Actions\Action::make('download_deliverable')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->submission_file))
                    ->url(fn ($record) => route('task.submission.download', ['id' => $record->submission_id]))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_evaluate')
                    ->label('Bulk Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->extraAttributes([
                        'style' => 'background-color: #1e40af !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; border-radius: 8px !important; font-weight: 600 !important;',
                    ])
                    ->modalHeading('Bulk Evaluate Selected Submissions')
                    ->modalDescription('Update evaluation status and timestamps for all selected submissions. Marks are not required for bulk evaluation.')
                    ->modalSubmitActionLabel('Save Bulk Evaluation')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Evaluation Status')
                            ->options([
                                'reviewed' => 'Mark as Reviewed',
                                'approved' => 'Approve Deliverables',
                                'rejected' => 'Reject / Revision Required',
                            ])
                            ->default('reviewed')
                            ->required()
                            ->native(false)
                            ->helperText('Select the status to apply to all selected intern deliverables.'),

                        Forms\Components\Textarea::make('admin_feedback')
                            ->label('Evaluation Remarks / Feedback (Optional)')
                            ->placeholder('Optional comment to record on all selected submissions...')
                            ->rows(3)
                            ->helperText('Leave empty to preserve existing feedback or provide general remarks.'),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $count = 0;
                        $status = $data['status'];
                        $feedback = filled($data['admin_feedback'] ?? null) ? $data['admin_feedback'] : null;

                        foreach ($records as $record) {
                            $updateData = [
                                'status'       => $status,
                                'evaluated_at' => now(),
                            ];

                            if ($feedback !== null) {
                                $updateData['admin_feedback'] = $feedback;
                            }

                            $record->update($updateData);

                            // Send notification to the intern
                            if ($record->intern) {
                                Notification::make()
                                    ->title('Your task submission has been ' . ($status === 'reviewed' ? 'reviewed' : ucfirst($status)) . '.')
                                    ->body($feedback ?? 'Your deliverable status has been updated by the admin team.')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->iconColor($status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'info'))
                                    ->sendToDatabase($record->intern);
                            }

                            $count++;
                        }

                        // Auto-complete tasks if all assigned interns are approved
                        $tasks = $records->map(fn ($r) => $r->task)->filter()->unique('task_id');
                        foreach ($tasks as $task) {
                            if (method_exists($task, 'checkAndAutoComplete')) {
                                $task->checkAndAutoComplete();
                            }
                        }

                        Notification::make()
                            ->title('Bulk Evaluation Complete')
                            ->body("Successfully updated {$count} " . ($count === 1 ? 'submission' : 'submissions') . ' to ' . ucfirst($status) . '.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
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
