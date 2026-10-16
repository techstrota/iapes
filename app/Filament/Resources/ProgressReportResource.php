<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgressReportResource\Pages;
use App\Models\ReportManagement\ProgressReport;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Section, Grid, Radio};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Notifications\Notification;
use ZipArchive;

class ProgressReportResource extends Resource
{
    protected static ?string $model = ProgressReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Progress Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(['default' => 1, 'lg' => 12])->schema([
                Section::make('Progress Report Parameters')
                    ->description('Set evaluation week duration and target group of interns.')
                    ->icon('heroicon-o-calendar-days')
                    ->columnSpan(['default' => 12, 'lg' => 7])
                    ->schema([
                        TextInput::make('title')
                            ->label('Report Title')
                            ->placeholder('e.g. Week 4 Progress Report — Frontend & UI Cohort')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            DatePicker::make('week_start_date')
                                ->label('Week Starting Date')
                                ->native(false)
                                ->displayFormat('d-m-Y')
                                ->prefixIcon('heroicon-o-calendar')
                                ->required(),

                            DatePicker::make('week_end_date')
                                ->label('Week Ending Date')
                                ->native(false)
                                ->displayFormat('d-m-Y')
                                ->prefixIcon('heroicon-o-calendar')
                                ->afterOrEqual('week_start_date')
                                ->required(),
                        ]),
                    ]),

                Section::make('Target Selection Engine')
                    ->description('Select recipients directly or by cohort/project team (same as Task Resource).')
                    ->icon('heroicon-o-user-group')
                    ->columnSpan(['default' => 12, 'lg' => 5])
                    ->schema([
                        Select::make('selection_mode')
                            ->label('Selection Mode')
                            ->options([
                                'intern' => '👥 Specific Interns (Direct Selection)',
                                'batch'  => '📦 Internship Batch (Bulk Select Cohort)',
                                'team'   => '🚀 Project Team / Squad (Bulk Select Squad)',
                            ])
                            ->default('intern')
                            ->live()
                            ->required()
                            ->native(false)
                            ->helperText('Choose how interns are enrolled into this progress report.'),

                        // 1. Direct Interns Multi-Select
                        Select::make('intern_ids')
                            ->label('Select Interns (Multi-Select)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('selection_mode') === 'intern')
                            ->required(fn ($get) => $get('selection_mode') === 'intern')
                            ->dehydrated(false)
                            ->options(function () {
                                return Intern::where('is_active', true)
                                    ->with(['batch', 'team'])
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($intern) {
                                        $details = array_filter([
                                            $intern->intern_code,
                                            $intern->domain,
                                            $intern->batch?->batch_name,
                                        ]);
                                        $suffix = !empty($details) ? ' (' . implode(' • ', $details) . ')' : '';
                                        return [$intern->id => $intern->name . $suffix];
                                    });
                            })
                            ->helperText('Search and select individual interns to include in this weekly report.'),

                        // 2. Select Batch
                        Select::make('batch_id')
                            ->label('Select Internship Batch')
                            ->relationship('batch', 'batch_name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('selection_mode') === 'batch')
                            ->required(fn ($get) => $get('selection_mode') === 'batch')
                            ->helperText('All active interns enrolled in this batch will be included.'),

                        // 3. Select Team
                        Select::make('team_id')
                            ->label('Select Project Team / Squad')
                            ->relationship('team', 'team_name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('selection_mode') === 'team')
                            ->required(fn ($get) => $get('selection_mode') === 'team')
                            ->helperText('All active interns in this squad will be included.'),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Report Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (ProgressReport $record): string => $record->formatted_date_range)
                    ->color('primary'),

                BadgeColumn::make('selection_mode')
                    ->label('Target Type')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'batch'  => 'Batch Cohort',
                        'team'   => 'Project Team',
                        default  => 'Direct Interns',
                    })
                    ->colors([
                        'info'    => 'batch',
                        'warning' => 'team',
                        'success' => 'intern',
                    ]),

                TextColumn::make('target_display')
                    ->label('Assigned Cohort / Squad')
                    ->state(function (ProgressReport $record): string {
                        if ($record->selection_mode === 'batch') {
                            return $record->batch?->batch_name ?? 'Batch';
                        }
                        if ($record->selection_mode === 'team') {
                            return $record->team?->team_name ?? 'Team';
                        }
                        return $record->total_interns_count . ' Interns';
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('evaluation_status')
                    ->label('Evaluation Progress')
                    ->state(function (ProgressReport $record): string {
                        $total = $record->total_interns_count;
                        $eval = $record->evaluated_count;
                        return "{$eval} / {$total} Evaluated";
                    })
                    ->badge()
                    ->color(fn (ProgressReport $record): string => 
                        $record->evaluated_count === $record->total_interns_count && $record->total_interns_count > 0 
                            ? 'success' 
                            : ($record->evaluated_count > 0 ? 'warning' : 'danger')
                    ),

                TextColumn::make('total_daily_logs_count')
                    ->label('Daily Logs')
                    ->state(fn (ProgressReport $record): string => $record->total_daily_logs_count . ' Logs')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Created On')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_evaluate')
                    ->label('View & Evaluate')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (ProgressReport $record): string => static::getUrl('view', ['record' => $record])),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info'),

                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Progress Report (PDF)')
                    ->modalDescription('Choose whether to download one combined PDF for all interns or separate PDFs inside a ZIP archive.')
                    ->modalSubmitActionLabel('Download Now')
                    ->form([
                        Radio::make('export_mode')
                            ->label('Export Format Mode')
                            ->options([
                                'single_pdf'    => 'Single PDF (All interns compiled into 1 document)',
                                'separate_pdfs' => 'Separate PDFs (Individual PDF for each intern in a ZIP file)',
                            ])
                            ->descriptions([
                                'single_pdf'    => 'Best for combined printing and unified weekly review.',
                                'separate_pdfs' => 'Creates an individual progress report PDF file per intern packed into a downloadable ZIP.',
                            ])
                            ->default('single_pdf')
                            ->required(),
                    ])
                    ->action(function (ProgressReport $record, array $data) {
                        return static::processPdfDownload($record, $data['export_mode'] ?? 'single_pdf');
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function processPdfDownload(ProgressReport $record, string $mode, ?int $singleInternId = null)
    {
        $record->loadMissing('batch');

        $reportInterns = $record->reportInterns()
            ->with(['intern', 'intern.batch', 'intern.team', 'intern.application', 'intern.offerletter'])
            ->get();

        if ($reportInterns->isEmpty()) {
            Notification::make()
                ->title('No Interns to Export')
                ->body('There are no interns assigned to this progress report.')
                ->warning()
                ->send();
            return null;
        }

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $watermarkPath = public_path('images/TsLogo_watermark_blurred.png');
        if (!file_exists($watermarkPath) && file_exists($logoPath)) {
            try {
                $im = @imagecreatefrompng($logoPath);
                if ($im) {
                    imagealphablending($im, false);
                    imagesavealpha($im, true);
                    for ($i = 0; $i < 12; $i++) {
                        imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
                    }
                    imagepng($im, $watermarkPath);
                    imagedestroy($im);
                }
            } catch (\Throwable $e) {}
        }

        $watermarkBase64 = file_exists($watermarkPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath))
            : $logoBase64;

        // ── 0. Single Intern (Current Selected Intern) ──
        if ($mode === 'current_intern' && $singleInternId) {
            $repIntern = $reportInterns->firstWhere('intern_id', $singleInternId);
            if (!$repIntern) {
                Notification::make()
                    ->title('Intern Not Found')
                    ->body('Selected intern is not enrolled in this progress report.')
                    ->danger()
                    ->send();
                return null;
            }

            $pdf = Pdf::loadView('pdf.intern-progress-report', [
                'report'          => $record,
                'reportInterns'   => collect([$repIntern]),
                'logoBase64'      => $logoBase64,
                'watermarkBase64' => $watermarkBase64,
                'isSingle'        => true,
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

            $safeName = str($repIntern->intern?->name ?? 'Intern')->slug()->toString();
            $safeCode = str_replace(['/', '\\'], '-', $repIntern->intern?->intern_code ?: $repIntern->intern_id);
            $fileName = "Progress_Report_{$safeName}_{$safeCode}.pdf";

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $fileName
            );
        }

        // ── 1. Single Combined PDF ──
        if ($mode === 'single_pdf') {
            $pdf = Pdf::loadView('pdf.intern-progress-report', [
                'report'          => $record,
                'reportInterns'   => $reportInterns,
                'logoBase64'      => $logoBase64,
                'watermarkBase64' => $watermarkBase64,
                'isSingle'        => false,
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

            $cleanTitle = str($record->title)->slug()->toString();
            $fileName = "Progress_Report_{$cleanTitle}_" . now()->format('Ymd') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $fileName
            );
        }

        // ── 2. Separate PDFs in ZIP ──
        $cleanTitle = str($record->title)->slug()->toString();
        $zipFileName = "Progress_Reports_{$cleanTitle}_" . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($reportInterns as $repIntern) {
                $intern = $repIntern->intern;
                $pdf = Pdf::loadView('pdf.intern-progress-report', [
                    'report'          => $record,
                    'reportInterns'   => collect([$repIntern]),
                    'logoBase64'      => $logoBase64,
                    'watermarkBase64' => $watermarkBase64,
                    'isSingle'        => true,
                ])
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

                $safeName = str($intern->name ?? 'Intern')->slug()->toString();
                $safeCode = str_replace(['/', '\\'], '-', $intern->intern_code ?: $intern->id);
                $docName = "Progress_Report_{$safeName}_{$safeCode}.pdf";

                $zip->addFromString($docName, $pdf->output());
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProgressReports::route('/'),
            'create' => Pages\CreateProgressReport::route('/create'),
            'view'   => Pages\ViewProgressReport::route('/{record}'),
            'edit'   => Pages\EditProgressReport::route('/{record}/edit'),
        ];
    }
}
