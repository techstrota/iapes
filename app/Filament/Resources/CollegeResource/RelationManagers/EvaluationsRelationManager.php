<?php

namespace App\Filament\Resources\CollegeResource\RelationManagers;

use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    protected static ?string $title = 'Internship Batches & Academic Evaluations';

    public function form(Form $form): Form
    {
        return CollegeEvaluationResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_title')
            ->columns([
                TextColumn::make('batch_title')
                    ->label('Batch / Cohort Title')
                    ->searchable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state ?: 'General Evaluation')
                    ->description(fn (CollegeEvaluation $record) => $record->academic_year ?: 'Session TBD'),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?: '1 Month'),

                TextColumn::make('total_marks')
                    ->label('Scheme')
                    ->formatStateUsing(function (CollegeEvaluation $record) {
                        $tot = rtrim(rtrim(number_format((float)$record->total_marks, 2), '0'), '.');
                        $att = rtrim(rtrim(number_format((float)$record->max_attendance_marks, 2), '0'), '.');
                        $task = rtrim(rtrim(number_format((float)$record->max_task_marks, 2), '0'), '.');
                        $parts = ["Att: {$att}", "Task: {$task}"];
                        foreach ($record->getCustomCriteriaList() as $crit) {
                            $cMax = rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.');
                            $parts[] = "{$crit['name']}: {$cMax}";
                        }
                        return "{$tot} M (" . implode(' | ', $parts) . ')';
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('interns_count')
                    ->label('Students')
                    ->counts('evaluationInterns')
                    ->badge()
                    ->color('success'),

                TextColumn::make('evaluation_date')
                    ->label('Evaluation Date')
                    ->date('d M Y'),

                TextColumn::make('is_archived')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Archived' : 'Active')
                    ->color(fn (bool $state) => $state ? 'warning' : 'success')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_batch')
                    ->label('New Evaluation Batch')
                    ->icon('heroicon-m-plus-circle')
                    ->color('primary')
                    ->url(fn () => CollegeEvaluationResource::getUrl('create', ['college_id' => $this->ownerRecord->id])),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archive Status')
                    ->placeholder('All Batches')
                    ->trueLabel('Archived Batches Only')
                    ->falseLabel('Active Batches Only'),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (CollegeEvaluation $record) => route('college-evaluations.download-pdf', $record->id)),



                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Archive Evaluation Batch')
                    ->modalDescription('Are you sure you want to move this batch to archive?')
                    ->visible(fn (CollegeEvaluation $record) => !$record->is_archived)
                    ->action(function (CollegeEvaluation $record) {
                        $record->archive();
                        Notification::make()->title('Batch Archived')->body("'{$record->batch_title}' has been moved to Archive.")->info()->send();
                    }),

                Tables\Actions\Action::make('unarchive')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Evaluation Batch')
                    ->modalDescription('Restore this batch back to active cohorts?')
                    ->visible(fn (CollegeEvaluation $record) => (bool) $record->is_archived)
                    ->action(function (CollegeEvaluation $record) {
                        $record->unarchive();
                        Notification::make()->title('Batch Restored')->body("'{$record->batch_title}' has been restored to Active batches.")->success()->send();
                    }),

                Tables\Actions\Action::make('edit_batch')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(fn (CollegeEvaluation $record) => CollegeEvaluationResource::getUrl('edit', ['record' => $record])),

                Tables\Actions\Action::make('view_evaluation')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (CollegeEvaluation $record) => CollegeEvaluationResource::getUrl('view', ['record' => $record])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->recordUrl(fn (CollegeEvaluation $record) => CollegeEvaluationResource::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc');
    }
}
