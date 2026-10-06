<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Pages;

use App\Filament\Resources\CandidateManagement\CandidateResource;
use App\Filament\Resources\CandidateManagement\CandidateResource\Widgets\CandidateStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CandidateStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('archiveCycle')
                ->label('Archive Cycle')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Current Recruitment Cycle')
                ->modalDescription('Archive candidate applications for this hiring round. Archived applications are preserved and can be viewed or restored at any time by selecting the cycle filter.')
                ->form([
                    \Filament\Forms\Components\TextInput::make('cohort_archive_name')
                        ->label('Recruitment Cycle Name')
                        ->placeholder('e.g. Cycle - ' . date('M Y') . ' Campus Drive')
                        ->default('Recruitment Cycle - ' . date('M Y'))
                        ->required(),

                    \Filament\Forms\Components\Select::make('target_status')
                        ->label('Which Candidates to Archive?')
                        ->options([
                            'all_active' => 'All Active Candidates (Regardless of stage)',
                            'completed' => 'Completed Candidates (Only Shortlisted & Rejected)',
                            'shortlisted' => 'Only Shortlisted Candidates',
                        ])
                        ->default('all_active')
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('archive_note')
                        ->label('Archive Note (Optional)')
                        ->placeholder('e.g. Completed hiring cycle for 2026')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $cycleName = trim($data['cohort_archive_name']);
                    $note = $data['archive_note'] ?? null;
                    $target = $data['target_status'];

                    $query = \App\Models\InterviewManagement\Application::active()
                        ->whereNotIn('status', ['pending', 'verified']);

                    if ($target === 'completed') {
                        $query->whereIn('status', ['shortlisted', 'rejected']);
                    } elseif ($target === 'shortlisted') {
                        $query->where('status', 'shortlisted');
                    }

                    $count = $query->count();
                    if ($count === 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('No matching candidates found to archive')
                            ->warning()
                            ->send();
                        return;
                    }

                    $query->update([
                        'is_archived' => true,
                        'cohort_archive_name' => $cycleName,
                        'archive_note' => $note,
                        'archived_at' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title("Archived {$count} Candidate(s) into '{$cycleName}'")
                        ->body('Active candidates view has been refreshed.')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Add Candidate')
                ->icon('heroicon-o-user-plus'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'applied';
    }

    public function getTabs(): array
    {
        $batchDateSub = \App\Models\InterviewManagement\InterviewBatch::select('interview_date')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        $batchTimeSub = \App\Models\InterviewManagement\InterviewBatch::select('start_time')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        $batchNameSub = \App\Models\InterviewManagement\InterviewBatch::select('interview_batch_name')
            ->join('interview_assignments', 'interview_assignments.interview_batch_id', '=', 'interview_batches.id')
            ->whereColumn('interview_assignments.application_id', 'applications.id')
            ->latest('interview_assignments.id')
            ->limit(1);

        return [
            'applied' => Tab::make('Applied')
                ->icon('heroicon-o-inbox')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'applied')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->where('status', 'applied')->count())
                ->badgeColor('gray'),

            'interview_scheduled' => Tab::make('Scheduled')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'interview_scheduled')
                    ->reorder()
                    ->orderByRaw('(' . $batchDateSub->toSql() . ') IS NULL ASC')
                    ->addBinding($batchDateSub->getBindings(), 'order')
                    ->orderBy($batchDateSub, 'asc')
                    ->orderBy($batchTimeSub, 'asc')
                    ->orderBy($batchNameSub, 'asc')
                    ->orderBy('application_code', 'asc')
                )
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->where('status', 'interview_scheduled')->count())
                ->badgeColor('info'),

            'interviewed' => Tab::make('Interviewed')
                ->icon('heroicon-o-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'interviewed')
                    ->reorder()
                    ->orderByRaw('(' . $batchDateSub->toSql() . ') IS NULL ASC')
                    ->addBinding($batchDateSub->getBindings(), 'order')
                    ->orderBy($batchDateSub, 'desc')
                    ->orderBy($batchTimeSub, 'desc')
                    ->orderBy($batchNameSub, 'asc')
                    ->orderBy('application_code', 'asc')
                )
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->where('status', 'interviewed')->count())
                ->badgeColor('warning'),

            'shortlisted' => Tab::make('Shortlisted')
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'shortlisted')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->where('status', 'shortlisted')->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Rejected')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->where('status', 'rejected')->count())
                ->badgeColor('danger'),

            'all' => Tab::make('All Candidates')
                ->icon('heroicon-o-users')
                ->modifyQueryUsing(fn (Builder $query) => $query->reorder()->latest('id'))
                ->badge(fn () => \App\Models\InterviewManagement\Application::active()->whereNotIn('status', ['pending', 'verified'])->count()),
        ];
    }
}
