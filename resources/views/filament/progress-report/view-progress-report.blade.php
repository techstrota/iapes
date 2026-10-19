<x-filament-panels::page>
@php
    $report = $this->record;
    $weekStart = \Carbon\Carbon::parse($report->week_start_date);
    $weekEnd = \Carbon\Carbon::parse($report->week_end_date);
    $formattedRange = $weekStart->format('d M Y') . ' — ' . $weekEnd->format('d M Y');

    // All report interns collection (strictly active & unarchived interns)
    $allReportInterns = $report->reportInterns()
        ->whereHas('intern', function ($q) {
            $q->where('is_active', true)
              ->whereNull('cohort_archive_name');
        })
        ->with(['intern', 'intern.batch', 'intern.team', 'intern.application', 'intern.offerletter'])
        ->get();

    $totalCount = $allReportInterns->count();
    $evaluatedCount = $allReportInterns->where('is_evaluated', true)->count();
    $pendingCount = $totalCount - $evaluatedCount;
    $completionPct = $totalCount > 0 ? round(($evaluatedCount / $totalCount) * 100) : 0;

    // Filter interns based on search & status filter
    $filteredInterns = $allReportInterns->filter(function ($item) {
        $matchesSearch = true;
        if (filled($this->searchQuery)) {
            $term = strtolower(trim($this->searchQuery));
            $name = strtolower($item->intern?->name ?? '');
            $code = strtolower($item->intern?->intern_code ?? '');
            $role = strtolower($item->intern?->internship_role ?? $item->intern?->domain ?? '');
            $matchesSearch = str_contains($name, $term) || str_contains($code, $term) || str_contains($role, $term);
        }

        $matchesStatus = true;
        if ($this->statusFilter === 'evaluated') {
            $matchesStatus = (bool) $item->is_evaluated;
        } elseif ($this->statusFilter === 'pending') {
            $matchesStatus = ! (bool) $item->is_evaluated;
        }

        return $matchesSearch && $matchesStatus;
    });

    // Selected intern record
    $currentRepIntern = $allReportInterns->firstWhere('intern_id', $this->selectedInternId) ?? $allReportInterns->first();
    $selectedIntern = $currentRepIntern?->intern;

    // Fetch daily logbooks for selected intern during the report week
    $dailyLogbooks = collect();
    $totalLoggedHours = 0;
    if ($selectedIntern) {
        $dailyLogbooks = \App\Models\InternManagement\DailyLogbook::where('intern_id', $selectedIntern->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date', 'asc')
            ->get();

        $totalLoggedHours = $dailyLogbooks->sum('hours_worked');
    }

    // Days in duration
    $weekDays = [];
    $currDate = $weekStart->copy();
    while ($currDate->lte($weekEnd)) {
        $weekDays[] = $currDate->copy();
        $currDate->addDay();
    }

    // Preload attendances & official holidays for selected intern during the duration
    $attendances = collect();
    $holidays = collect();
    if ($selectedIntern) {
        $attendances = \App\Models\Attendance::where('intern_id', $selectedIntern->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->date)->toDateString());

        $holidays = \App\Models\Attendance::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('status', 'holiday')
            ->where(function ($q) use ($selectedIntern) {
                $q->where('intern_id', $selectedIntern->id)
                  ->orWhereNull('intern_id');
            })
            ->get()
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->date)->toDateString());
    }

    // Resolve scheduled batch shift timing for selected intern
    $batch = $selectedIntern?->batch ?? $report->batch;
    $defaultBatchStart = '11:00 AM';
    $defaultBatchEnd   = '04:00 PM';
    if ($batch && filled($batch->batch_timing)) {
        $timingParts = preg_split('/\s+(?:to|-)\s+/i', trim($batch->batch_timing));
        if (count($timingParts) >= 2) {
            try {
                $defaultBatchStart = \Carbon\Carbon::parse(trim($timingParts[0]))->format('g:i A');
                $defaultBatchEnd   = \Carbon\Carbon::parse(trim($timingParts[1]))->format('g:i A');
            } catch (\Throwable $e) {
                $defaultBatchStart = trim($timingParts[0]);
                $defaultBatchEnd   = trim($timingParts[1]);
            }
        }
    }

    // Tally attendance summary counts
    $attendanceCounts = [
        'present'  => 0,
        'late'     => 0,
        'wfh'      => 0,
        'leave'    => 0,
        'absent'   => 0,
        'holiday'  => 0,
        'week_off' => 0,
        'unmarked' => 0,
    ];
    $totalLateMinutes = 0;

    $weekRows = [];
    foreach ($weekDays as $dayCarbon) {
        $dateStr = $dayCarbon->toDateString();
        $att = $attendances->get($dateStr);
        $hol = $holidays->get($dateStr);
        $log = $dailyLogbooks->first(fn ($l) => \Carbon\Carbon::parse($l->date)->toDateString() === $dateStr);

        $status = 'unmarked';
        $note = null;
        $lateMins = null;
        $startTime = null;
        $endTime = null;

        if ($att) {
            $status = strtolower($att->status);
            $note = $att->note;
            $lateMins = $att->late_minutes;
            $startTime = $att->start_time;
            $endTime = $att->end_time;
        } elseif ($hol) {
            $status = 'holiday';
            $note = $hol->note;
        } elseif ($dayCarbon->isSunday()) {
            $status = 'week_off';
        }

        // Fallback default times for active working days if not explicitly set
        if (!$startTime && in_array($status, ['present', 'late', 'wfh'])) {
            $startTime = $defaultBatchStart;
        }
        if (!$endTime && in_array($status, ['present', 'late', 'wfh'])) {
            $endTime = $defaultBatchEnd;
        }

        if (isset($attendanceCounts[$status])) {
            $attendanceCounts[$status]++;
        } else {
            $attendanceCounts['unmarked']++;
        }

        if ($status === 'late' && $lateMins > 0) {
            $totalLateMinutes += (int) $lateMins;
        }

        $weekRows[] = [
            'carbon'     => $dayCarbon,
            'date_str'   => $dateStr,
            'status'     => $status,
            'note'       => $note,
            'late_mins'  => $lateMins,
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'att'        => $att,
            'log'        => $log,
        ];
    }
@endphp

<style>
    /* ── Stitch Dark Theme Progress Report Workspace ── */
    .pr-card {
        background-color: #131b2e;
        border: 1.5px solid #222a3d;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }
    .pr-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .pr-textarea {
        width: 100%;
        background-color: #0b1326;
        border: 1.5px solid #222a3d;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 13.5px;
        color: #dae2fd;
        outline: none;
        transition: all 0.2s ease;
        line-height: 1.6;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
    }
    .pr-textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.4);
    }
    .pr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }
    .pr-table th {
        background-color: #0b1326;
        color: #8e909f;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 14px;
        border-bottom: 1.5px solid #222a3d;
    }
    .pr-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #1c2638;
        vertical-align: top;
        color: #dae2fd;
    }
    .pr-table tr:hover td {
        background-color: rgba(59, 130, 246, 0.03);
    }
    .pr-table tr:last-child td {
        border-bottom: none;
    }

    /* ── Timing Chip Interactive Cell ── */
    .timing-chip {
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .timing-chip:hover {
        border-color: #3b82f6 !important;
        background-color: rgba(59, 130, 246, 0.08) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    /* ── Sleek Modal Custom Scrollbar ── */
    .modal-scrollable-body {
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #2b354f #131b2e;
        overscroll-behavior: contain;
    }
    .modal-scrollable-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-scrollable-body::-webkit-scrollbar-track {
        background: #131b2e;
        border-radius: 4px;
    }
    .modal-scrollable-body::-webkit-scrollbar-thumb {
        background-color: #2b354f;
        border-radius: 9999px;
    }
    .modal-scrollable-body::-webkit-scrollbar-thumb:hover {
        background-color: #3b82f6;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 20px;">

    {{-- ── 1. Top Report Header & Info Banner ── --}}
    <div class="pr-card" style="padding: 22px 26px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; top: -50px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                        {{ $report->title }}
                    </h1>
                    <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);">
                        🗓️ {{ $formattedRange }}
                    </span>
                    <span class="pr-badge" style="background: {{ $report->status === 'completed' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)' }}; color: {{ $report->status === 'completed' ? '#34d399' : '#fbbf24' }}; border: 1px solid {{ $report->status === 'completed' ? 'rgba(16, 185, 129, 0.4)' : 'rgba(245, 158, 11, 0.4)' }};">
                        {{ $report->status === 'completed' ? '✓ All Evaluated' : '⏳ Evaluation In Progress' }}
                    </span>
                </div>
                <div style="font-size: 13px; color: #8e909f; margin-top: 6px;">
                    Target: <strong style="color: #cbd5e1;">{{ ucfirst($report->selection_mode) }} Mode</strong> 
                    @if ($report->batch) • Batch: <strong style="color: #60a5fa;">{{ $report->batch->batch_name }}</strong> @endif
                    @if ($report->team) • Team: <strong style="color: #c084fc;">{{ $report->team->team_name }}</strong> @endif
                    • Created by <strong style="color: #cbd5e1;">{{ $report->creator?->name ?? 'Admin' }}</strong>
                </div>
            </div>

            {{-- Header Actions: Edit Report & Download PDF Quick Triggers --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a
                    href="{{ \App\Filament\Resources\ProgressReportResource::getUrl('edit', ['record' => $report]) }}"
                    style="background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1.5px solid rgba(59, 130, 246, 0.35); font-size: 13px; font-weight: 700; padding: 9px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all 0.15s ease;"
                    onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.22)'; this.style.borderColor='#3b82f6';"
                    onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.12)'; this.style.borderColor='rgba(59, 130, 246, 0.35)';">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Report</span>
                </a>

                <button
                    type="button"
                    wire:click="openPdfModal"
                    style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); transition: transform 0.15s ease;"
                    onmouseover="this.style.transform='scale(1.03)';"
                    onmouseout="this.style.transform='scale(1)';">
                    <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Download PDF</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 2. Top KPI Statistics Row (4 Responsive Cards) ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px;">
        
        {{-- Total Interns --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Interns</div>
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.1; margin-top: 2px;">{{ $totalCount }}</div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">Enrolled in report</div>
            </div>
        </div>

        {{-- Evaluated Interns --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div style="flex-grow: 1;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Evaluated</div>
                    <span style="font-size: 11px; font-weight: 700; color: #34d399;">{{ $completionPct }}%</span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; line-height: 1.1; margin-top: 2px;">{{ $evaluatedCount }}</div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">Summary & Remarks filled</div>
            </div>
        </div>

        {{-- Pending Evaluation --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Pending Review</div>
                <div style="font-size: 26px; font-weight: 800; color: {{ $pendingCount > 0 ? '#fbbf24' : '#34d399' }}; line-height: 1.1; margin-top: 2px;">{{ $pendingCount }}</div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">Awaiting supervisor review</div>
            </div>
        </div>

        {{-- Weekly Daily Logs Submitted --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Weekly Daily Logs</div>
                <div style="font-size: 26px; font-weight: 800; color: #60a5fa; line-height: 1.1; margin-top: 2px;">{{ $report->total_daily_logs_count }}</div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">Total tasks submitted by interns</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Master-Detail Split Workspace ── --}}
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: start;">
        
        {{-- ── LEFT PANE: Interns Directory Selector List ── --}}
        <div class="pr-card" style="padding: 18px; display: flex; flex-direction: column; gap: 14px;">
            
            {{-- Header & Count --}}
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 14px; font-weight: 800; color: #ffffff;">
                    Selected Interns ({{ $totalCount }})
                </div>
                <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                    {{ $evaluatedCount }}/{{ $totalCount }} Evaluated
                </span>
            </div>

            {{-- Filter Tabs (All / Evaluated / Pending) --}}
            <div style="display: flex; background: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 3px; gap: 2px;">
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'all')"
                    style="flex: 1; padding: 6px 8px; font-size: 11.5px; font-weight: 700; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $statusFilter === 'all' ? 'background-color: #1e40af; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    All ({{ $totalCount }})
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'evaluated')"
                    style="flex: 1; padding: 6px 8px; font-size: 11.5px; font-weight: 700; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $statusFilter === 'evaluated' ? 'background-color: #059669; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    Done ({{ $evaluatedCount }})
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'pending')"
                    style="flex: 1; padding: 6px 8px; font-size: 11.5px; font-weight: 700; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $statusFilter === 'pending' ? 'background-color: #d97706; color: #ffffff;' : 'background-color: transparent; color: #8e909f;' }}">
                    Pending ({{ $pendingCount }})
                </button>
            </div>

            {{-- Search Input --}}
            <div style="position: relative;">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="searchQuery"
                    placeholder="Search name or ID..."
                    style="width: 100%; background: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 8px 12px 8px 34px; font-size: 12.5px; color: #dae2fd; outline: none; box-sizing: border-box;"
                />
                <svg style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Interns Cards Scrollable List --}}
            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 650px; overflow-y: auto; padding-right: 4px;">
                @forelse ($filteredInterns as $repIntern)
                    @php
                        $internObj = $repIntern->intern;
                        $isSelected = ($this->selectedInternId === $internObj?->id);
                        $isEval = (bool) $repIntern->is_evaluated;

                        // Initials fallback
                        $internName = $internObj?->name ?: ($internObj?->offerletter?->name ?? 'Intern');
                        $words = explode(' ', trim($internName));
                        $initials = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
                    @endphp

                    <div
                        wire:click="selectIntern({{ $internObj?->id }})"
                        style="padding: 12px 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; justify-content: space-between; gap: 10px; border: 1.5px solid {{ $isSelected ? '#3b82f6' : '#1c2638' }}; background-color: {{ $isSelected ? 'rgba(59, 130, 246, 0.12)' : '#0b1326' }}; box-shadow: {{ $isSelected ? '0 0 0 1px #3b82f6, 0 4px 12px rgba(59, 130, 246, 0.2)' : 'none' }};"
                        onmouseover="if (!{{ $isSelected ? 'true' : 'false' }}) { this.style.borderColor='#334155'; }"
                        onmouseout="if (!{{ $isSelected ? 'true' : 'false' }}) { this.style.borderColor='{{ $isSelected ? '#3b82f6' : '#1c2638' }}'; }">
                        
                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                            {{-- Avatar --}}
                            <div style="width: 38px; height: 38px; border-radius: 50%; overflow: hidden; background-color: #172235; color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; border: 1px solid #233149;">
                                @if ($internObj?->intern_image)
                                    <img src="{{ asset('storage/' . $internObj->intern_image) }}" alt="{{ $internName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                @else
                                    {{ $initials ?: 'IN' }}
                                @endif
                            </div>

                            <div style="min-width: 0;">
                                <div style="font-size: 13.5px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $internName }}
                                </div>
                                <div style="font-size: 11px; font-weight: 600; color: #60a5fa; font-family: ui-monospace, monospace; margin-top: 2px;">
                                    {{ $internObj?->intern_code ?: ('INT-' . $internObj?->id) }}
                                </div>
                            </div>
                        </div>

                        {{-- Evaluated Status Pill --}}
                        <div style="flex-shrink: 0;">
                            @if ($isEval)
                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                    ✓ Done
                                </span>
                            @else
                                <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                    ⏳ Pending
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding: 24px 16px; text-align: center; color: #64748b; font-size: 13px;">
                        No interns match your search filter.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── RIGHT PANE: Selected Intern Progress & Supervisor Evaluation Workspace ── --}}
        @if ($selectedIntern)
            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                {{-- Intern Profile Hero Header Card --}}
                <div class="pr-card" style="padding: 20px 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            {{-- Circular Avatar --}}
                            <div style="width: 52px; height: 52px; border-radius: 50%; overflow: hidden; background-color: #172235; color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; flex-shrink: 0; border: 2px solid #1e40af;">
                                @if ($selectedIntern->intern_image)
                                    <img src="{{ asset('storage/' . $selectedIntern->intern_image) }}" alt="{{ $selectedIntern->name }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                @else
                                    {{ strtoupper(substr($selectedIntern->name ?? 'I', 0, 2)) }}
                                @endif
                            </div>

                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">
                                        {{ $selectedIntern->name }}
                                    </h2>
                                    <span style="font-family: ui-monospace, monospace; font-size: 12px; font-weight: 700; color: #60a5fa; background: #0b1326; border: 1px solid #1d4ed8; padding: 2px 8px; border-radius: 6px;">
                                        {{ $selectedIntern->intern_code ?: ('INT-' . $selectedIntern->id) }}
                                    </span>
                                </div>
                                <div style="font-size: 12.5px; color: #94a3b8; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>Role: <strong style="color: #cbd5e1;">{{ $selectedIntern->internship_role ?: ($selectedIntern->domain ?: 'Intern') }}</strong></span>
                                    <span>•</span>
                                    <span>College: <strong style="color: #cbd5e1;">{{ $selectedIntern->college ?: ($selectedIntern->university ?: 'N/A') }}</strong></span>
                                    @if ($selectedIntern->batch)
                                        <span>•</span>
                                        <span style="color: #60a5fa;">🗓️ {{ $selectedIntern->batch->batch_name }}</span>
                                    @endif
                                    @if ($selectedIntern->team)
                                        <span>•</span>
                                        <span style="color: #c084fc;">🚀 {{ $selectedIntern->team->team_name }}</span>
                                        @if ($selectedIntern->team->mentor_name)
                                            <span>•</span>
                                            <span style="color: #38bdf8;">👤 Mentor: <strong style="color: #e0f2fe;">{{ $selectedIntern->team->mentor_name }}</strong></span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Evaluated Status Badge --}}
                        <div>
                            @if ($currentRepIntern?->is_evaluated)
                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 12px; padding: 6px 14px;">
                                    ✓ Evaluation Completed
                                </span>
                            @else
                                <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 12px; padding: 6px 14px;">
                                    ⏳ Evaluation Pending
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Section 1: Daily Attendance & Tasks Filled by Intern (Collapsible with Dropdown Toggle) --}}
                <div class="pr-card" style="padding: 22px 24px;">
                    {{-- Section Header & Dropdown View Toggle --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-bottom: {{ $showDailyTasksSection ? '14px' : '0' }}; {{ $showDailyTasksSection ? 'border-bottom: 1.5px solid #222a3d;' : '' }}">
                        <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" wire:click="toggleDailyTasksSection">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <h3 style="font-size: 16.5px; font-weight: 800; color: #ffffff; margin: 0;">
                                        Daily Tasks & Attendance Log
                                    </h3>
                                    <span class="pr-badge" style="background: rgba(99, 102, 241, 0.12); color: #b8c4ff; border: 1px solid rgba(99, 102, 241, 0.3);">
                                        🗓️ Shift: {{ $defaultBatchStart }} — {{ $defaultBatchEnd }}
                                    </span>
                                    @if ($showDailyTasksSection)
                                        <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                            ✓ Form Enabled
                                        </span>
                                    @else
                                        <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                            ✕ Display Off (Default)
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 3px;">
                                    Date-by-date breakdown of verification status, tasks, and deliverables for {{ $selectedIntern->name }} ({{ $formattedRange }})
                                </div>
                            </div>
                        </div>

                        {{-- Dropdown Selector above Section --}}
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 6px; background: #080e1d; border: 1.5px solid {{ $showDailyTasksSection ? '#3b82f6' : '#222a3d' }}; border-radius: 9px; padding: 4px 10px;">
                                <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">View:</span>
                                <select
                                    wire:model.live="showDailyTasksSection"
                                    style="background: transparent; border: none; font-size: 12px; font-weight: 700; color: {{ $showDailyTasksSection ? '#60a5fa' : '#94a3b8' }}; outline: none; cursor: pointer; padding: 2px 4px;">
                                    <option value="0" style="background: #0b1326; color: #cbd5e1;">✕ Off / Collapsed (Default)</option>
                                    <option value="1" style="background: #0b1326; color: #60a5fa;">👁️ On / Enable Daily Tasks & Attendance</option>
                                </select>
                            </div>

                            <button
                                type="button"
                                wire:click="toggleDailyTasksSection"
                                style="background: {{ $showDailyTasksSection ? 'rgba(59, 130, 246, 0.15)' : '#171f33' }}; color: {{ $showDailyTasksSection ? '#93c5fd' : '#cbd5e1' }}; border: 1px solid {{ $showDailyTasksSection ? 'rgba(59, 130, 246, 0.4)' : '#25334d' }}; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                                <span>{{ $showDailyTasksSection ? '▲ Hide Log' : '▼ Show Log' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- If Dropdown is enabled (ON), display the full breakdown & table --}}
                    @if ($showDailyTasksSection)
                        {{-- Quick Metrics Badges --}}
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; margin-top: 14px; margin-bottom: 12px;">
                            <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35);">
                                📝 {{ $dailyLogbooks->count() }}/{{ count($weekDays) }} Days Logged
                            </span>
                            <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                ⏱️ {{ $totalLoggedHours }} Total Hours
                            </span>
                            @if ($attendanceCounts['late'] > 0)
                                <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                    ⚠️ {{ $attendanceCounts['late'] }} Late ({{ floor($totalLateMinutes / 60) > 0 ? floor($totalLateMinutes / 60) . 'h ' : '' }}{{ ($totalLateMinutes % 60) }}m delay)
                                </span>
                            @endif
                        </div>

                        {{-- Comprehensive Table for All Dates in Duration (Timing column hidden outside, visible in Edit modal) --}}
                        <div style="overflow-x: auto; border: 1.5px solid #1c2638; border-radius: 12px; background-color: #0b1326; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);">
                            <table class="pr-table">
                                <thead>
                                    <tr>
                                        <th style="width: 130px;">Date & Day</th>
                                        <th style="width: 165px;">Attendance Status</th>
                                        <th>Tasks & Deliverables Completed (Intern Log)</th>
                                        <th style="width: 120px; text-align: center;">Status & Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($weekRows as $row)
                                        @php
                                            $d = $row['carbon'];
                                            $dStr = $row['date_str'];
                                            $st = $row['status'];
                                            $attNote = $row['note'];
                                            $lateMins = $row['late_mins'];
                                            $startTime = $row['start_time'];
                                            $endTime = $row['end_time'];
                                            $att = $row['att'];
                                            $log = $row['log'];
                                            $isToday = $d->isToday();
                                        @endphp
                                        <tr style="{{ $st === 'week_off' ? 'background-color: rgba(139, 92, 246, 0.02);' : ($st === 'holiday' ? 'background-color: rgba(245, 158, 11, 0.02);' : ($st === 'late' ? 'background-color: rgba(245, 158, 11, 0.03);' : '')) }}">
                                            {{-- 1. Date & Day --}}
                                            <td>
                                                <div style="font-weight: 800; color: #ffffff; font-size: 13px; display: flex; align-items: center; gap: 5px;">
                                                    <span>{{ $d->format('D') }}</span>
                                                    @if ($isToday)
                                                        <span style="font-size: 9.5px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 4px; padding: 0 4px; font-weight: 700;">TODAY</span>
                                                    @endif
                                                </div>
                                                <div style="font-size: 11px; color: #94a3b8; font-family: ui-monospace, monospace; margin-top: 2px;">
                                                    {{ $d->format('d M Y') }}
                                                </div>
                                            </td>

                                            {{-- 2. Attendance Status Badge & Notes --}}
                                            <td>
                                                @if ($st === 'present')
                                                    <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10B981;"></span>
                                                        Present
                                                    </span>
                                                    <div style="font-size: 11px; color: #4edea3; margin-top: 3px; font-weight: 600;">Physical / Onsite</div>
                                                @elseif ($st === 'late')
                                                    <span class="pr-badge" style="background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #F59E0B;"></span>
                                                        Late Arrival
                                                    </span>
                                                    @if ($lateMins !== null && $lateMins > 0)
                                                        <div style="font-size: 11px; font-weight: 800; color: #f59e0b; margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                                                            <span>⚠️ Late by {{ floor($lateMins / 60) > 0 ? floor($lateMins / 60) . 'h ' : '' }}{{ ($lateMins % 60) }}m</span>
                                                        </div>
                                                    @endif
                                                    @if ($attNote)
                                                        <div style="font-size: 11px; color: #fbbf24; background: rgba(245, 158, 11, 0.1); border-left: 2px solid #f59e0b; padding: 3px 6px; border-radius: 4px; margin-top: 4px; line-height: 1.35; font-style: italic;">
                                                            💬 "{{ $attNote }}"
                                                        </div>
                                                    @endif
                                                @elseif ($st === 'wfh')
                                                    <span class="pr-badge" style="background: rgba(2, 132, 199, 0.15); color: #93c5fd; border: 1px solid rgba(2, 132, 199, 0.35);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #0284C7;"></span>
                                                        WFH
                                                    </span>
                                                    <div style="font-size: 11px; color: #93c5fd; margin-top: 3px;">Approved Remote</div>
                                                @elseif ($st === 'leave')
                                                    <span class="pr-badge" style="background: rgba(147, 51, 234, 0.15); color: #d8b4fe; border: 1px solid rgba(147, 51, 234, 0.35);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #9333EA;"></span>
                                                        On Leave
                                                    </span>
                                                    @if ($attNote)
                                                        <div style="font-size: 11px; color: #d8b4fe; margin-top: 3px; font-style: italic;">💬 "{{ $attNote }}"</div>
                                                    @endif
                                                @elseif ($st === 'absent')
                                                    <span class="pr-badge" style="background: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.35);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #EF4444;"></span>
                                                        Absent
                                                    </span>
                                                    @if ($attNote)
                                                        <div style="font-size: 11px; color: #ffb4ab; margin-top: 3px; font-style: italic;">💬 "{{ $attNote }}"</div>
                                                    @endif
                                                @elseif ($st === 'holiday')
                                                    <span class="pr-badge" style="background: rgba(139, 92, 246, 0.15); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.35);">
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #8b5cf6;"></span>
                                                        Holiday
                                                    </span>
                                                    @if ($attNote)
                                                        <div style="font-size: 10.5px; color: #c084fc; margin-top: 3px; font-style: italic;">{{ $attNote }}</div>
                                                    @endif
                                                @elseif ($st === 'week_off')
                                                    <span class="pr-badge" style="background: rgba(139, 92, 246, 0.12); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3);">
                                                        ☕ Sunday Off
                                                    </span>
                                                @else
                                                    <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                                        — Not Marked
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- 3. Tasks & Deliverables Completed (Intern Log + Supervisor Remarks) --}}
                                            <td>
                                                @if ($log)
                                                    @if ($log->title)
                                                        <div style="font-weight: 700; color: #60a5fa; font-size: 12.5px; margin-bottom: 4px;">
                                                            📌 {{ $log->title }}
                                                        </div>
                                                    @endif
                                                    <div style="white-space: pre-line; line-height: 1.5; color: #dae2fd; font-size: 12.5px;">
                                                        {{ $log->tasks_completed }}
                                                    </div>
                                                    <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                                                        <span class="pr-badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; font-size: 10.5px;">
                                                            ⏱️ {{ $log->hours_worked ?? 8.0 }} hrs logged
                                                        </span>
                                                    </div>
                                                    {{-- Supervisor Remark Display if present --}}
                                                    @if ($log->supervisor_remarks)
                                                        <div style="margin-top: 6px; font-size: 11.5px; color: #93c5fd; background: rgba(59, 130, 246, 0.08); border-left: 3px solid #3b82f6; padding: 4px 8px; border-radius: 4px; line-height: 1.4;">
                                                            💬 <strong style="color: #60a5fa;">Supervisor Remark:</strong> {{ $log->supervisor_remarks }}
                                                        </div>
                                                    @endif
                                                @else
                                                    @if ($st === 'holiday')
                                                        <div style="color: #fbbf24; font-size: 12px; font-style: italic;">
                                                            🎉 Official Holiday — No work scheduled{{ $attNote ? ' (' . $attNote . ')' : '' }}
                                                        </div>
                                                    @elseif ($st === 'week_off')
                                                        <div style="color: #a78bfa; font-size: 12px; font-style: italic;">
                                                            ☕ Scheduled Sunday Week-Off
                                                        </div>
                                                    @elseif ($st === 'leave')
                                                        <div style="color: #c084fc; font-size: 12px; font-style: italic;">
                                                            🌴 Intern on Approved Leave{{ $attNote ? ' (' . $attNote . ')' : '' }}
                                                        </div>
                                                    @elseif ($st === 'absent')
                                                        <div style="color: #f87171; font-size: 12px; font-style: italic;">
                                                            ✕ Intern Marked Absent{{ $attNote ? ' (' . $attNote . ')' : '' }}
                                                        </div>
                                                    @elseif ($st === 'late')
                                                        <div style="color: #fbbf24; font-size: 12px; font-style: italic;">
                                                            ⏱️ Marked Late Arrival{{ $attNote ? ' — ' . $attNote : '' }}. No daily task log submitted.
                                                        </div>
                                                    @else
                                                        <div style="color: #64748b; font-size: 12px; font-style: italic;">
                                                            No logbook submitted for this day
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- 4. Status & Action (Opens Edit Modal containing timing, tasks & remarks) --}}
                                            <td style="text-align: center;">
                                                @if ($log)
                                                    <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 10.5px;">
                                                        ✓ {{ ucfirst($log->status ?? 'Submitted') }}
                                                    </span>
                                                @elseif ($st === 'holiday' || $st === 'week_off')
                                                    <span class="pr-badge" style="background: rgba(148, 163, 184, 0.08); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); font-size: 10.5px;">
                                                        Exempt
                                                    </span>
                                                @elseif ($st === 'leave')
                                                    <span class="pr-badge" style="background: rgba(168, 85, 247, 0.1); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.25); font-size: 10.5px;">
                                                        On Leave
                                                    </span>
                                                @elseif ($st === 'late')
                                                    <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 10.5px;">
                                                        Late Verified
                                                    </span>
                                                @else
                                                    <span class="pr-badge" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 10.5px;">
                                                        Missing
                                                    </span>
                                                @endif

                                                <div style="margin-top: 6px;">
                                                    <button
                                                        type="button"
                                                        wire:click="openTimingModal('{{ $dStr }}')"
                                                        title="Edit timing, tasks, deliverables & attendance for {{ $d->format('d M') }}"
                                                        style="background: rgba(59, 130, 246, 0.12); border: 1.5px solid #2563eb; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 700; color: #93c5fd; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s ease;"
                                                        onmouseover="this.style.backgroundColor='#1d4ed8'; this.style.color='#ffffff';"
                                                        onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.12)'; this.style.color='#93c5fd';">
                                                        <span>✏️ Edit</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #080e1d; border-top: 1.5px solid #222a3d; font-size: 12px;">
                                        <td colspan="3" style="padding: 12px 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-weight: 700; color: #ffffff; font-size: 12px;">Attendance Breakdown:</span>
                                                @if ($attendanceCounts['present'] > 0)
                                                    <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">{{ $attendanceCounts['present'] }} Present</span>
                                                @endif
                                                @if ($attendanceCounts['late'] > 0)
                                                    <span class="pr-badge" style="background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                                        {{ $attendanceCounts['late'] }} Late ({{ floor($totalLateMinutes / 60) > 0 ? floor($totalLateMinutes / 60) . 'h ' : '' }}{{ ($totalLateMinutes % 60) }}m delay)
                                                    </span>
                                                @endif
                                                @if ($attendanceCounts['wfh'] > 0)
                                                    <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">{{ $attendanceCounts['wfh'] }} WFH</span>
                                                @endif
                                                @if ($attendanceCounts['leave'] > 0)
                                                    <span class="pr-badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">{{ $attendanceCounts['leave'] }} Leave</span>
                                                @endif
                                                @if ($attendanceCounts['absent'] > 0)
                                                    <span class="pr-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">{{ $attendanceCounts['absent'] }} Absent</span>
                                                @endif
                                                @if ($attendanceCounts['holiday'] > 0)
                                                    <span class="pr-badge" style="background: rgba(139, 92, 246, 0.15); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.3);">{{ $attendanceCounts['holiday'] }} Holiday</span>
                                                @endif
                                                @if ($attendanceCounts['week_off'] > 0)
                                                    <span class="pr-badge" style="background: rgba(139, 92, 246, 0.12); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3);">{{ $attendanceCounts['week_off'] }} Sunday Off</span>
                                                @endif
                                                @if ($attendanceCounts['unmarked'] > 0)
                                                    <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">{{ $attendanceCounts['unmarked'] }} Unmarked</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="padding: 12px 14px; text-align: center; font-weight: 700; color: #94a3b8; font-size: 11px;">
                                            {{ $dailyLogbooks->count() }}/{{ count($weekDays) }} Logged
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        {{-- Collapsed State Placeholder Banner (Click to Enable) --}}
                        <div
                            wire:click="toggleDailyTasksSection"
                            style="margin-top: 14px; padding: 18px 20px; background: #080e1d; border: 1.5px dashed #222a3d; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; transition: all 0.15s ease;"
                            onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.04)';"
                            onmouseout="this.style.borderColor='#222a3d'; this.style.backgroundColor='#080e1d';">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                    📋
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: #cbd5e1;">
                                        Daily Tasks & Attendance Log is hidden by default.
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                        Click here or select from the dropdown above to enable and view {{ $dailyLogbooks->count() }}/{{ count($weekDays) }} days of records.
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click.stop="toggleDailyTasksSection"
                                style="font-size: 12px; font-weight: 700; color: #60a5fa; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35); padding: 6px 14px; border-radius: 7px; cursor: pointer;">
                                Enable & View Logs ▼
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Section 2: Supervisor Weekly Evaluation Form --}}
                <div class="pr-card" style="padding: 22px 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #222a3d;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                                    Supervisor Evaluation & Remarks
                                </h3>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">
                                    Provide the weekly summary and remarks for {{ $selectedIntern->name }}
                                </div>
                            </div>
                        </div>

                        {{-- Auto-generate summary helper button --}}
                        @if ($dailyLogbooks->count() > 0)
                            <button
                                type="button"
                                wire:click="autoDraftSummary"
                                style="background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.15s ease;"
                                title="Synthesize weekly summary from intern's daily tasks">
                                <span>⚡ Auto-Draft Summary from Tasks</span>
                            </button>
                        @endif
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 18px;">
                        
                        {{-- Weekly Summary Input --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                Weekly Summary:
                            </label>
                            <textarea
                                wire:model="weeklySummary"
                                rows="5"
                                placeholder="Summarize deliverables completed, overall performance, code quality, and attendance for this week..."
                                class="pr-textarea"
                            ></textarea>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                                Outline the key milestones and performance highlights achieved by the intern this week.
                            </div>
                        </div>

                        {{-- Supervisor Remarks Input --}}
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                Supervisor Remarks:
                            </label>
                            <textarea
                                wire:model="supervisorRemarks"
                                rows="4"
                                placeholder="Mentor remarks, feedback on accuracy/speed, areas for improvement, and directives for the upcoming week..."
                                class="pr-textarea"
                            ></textarea>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                                Specific feedback or recommendations from the supervisor/coordinator.
                            </div>
                        </div>

                        {{-- Bottom Save Actions Bar --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-top: 10px; border-top: 1px solid #1c2638;">
                            <div>
                                @if ($lastSavedAt)
                                    <span style="font-size: 12px; color: #34d399; display: inline-flex; align-items: center; gap: 5px;">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Last saved: {{ $lastSavedAt }}</span>
                                    </span>
                                @else
                                    <span style="font-size: 12px; color: #8e909f;">
                                        Enter weekly summary & remarks and click save.
                                    </span>
                                @endif
                            </div>

                            <button
                                type="button"
                                wire:click="saveRemarks"
                                style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; border: none; font-size: 13.5px; font-weight: 700; padding: 10px 24px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 16px rgba(30, 64, 175, 0.4); transition: transform 0.15s ease;"
                                onmouseover="this.style.transform='scale(1.02)';"
                                onmouseout="this.style.transform='scale(1)';">
                                <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <span>Save Evaluation & Remarks</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        @else
            <div class="pr-card" style="padding: 48px 24px; text-align: center; color: #8e909f;">
                @if ($allReportInterns->isEmpty())
                    <div style="font-size: 36px; margin-bottom: 12px;">📁</div>
                    <div style="font-size: 15px; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">No Active Interns in this Report</div>
                    <div style="font-size: 12.5px; color: #64748b; max-width: 440px; margin: 0 auto;">All interns originally enrolled in this progress report have been promoted to completion or moved to archive.</div>
                @else
                    <div style="font-size: 14px; font-weight: 600; color: #94a3b8;">Select an intern from the left list to review their daily tasks and write weekly evaluations.</div>
                @endif
            </div>
        @endif
    </div>

    {{-- ── PDF Export Modal ── --}}
    @if ($showPdfModal)
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 500px; padding: 26px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closePdfModal">
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Export Progress Report</h3>
                    </div>
                    <button type="button" wire:click="closePdfModal" style="background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer;">&times;</button>
                </div>

                <p style="font-size: 13px; color: #8e909f; margin: 0 0 18px 0;">
                    Choose your export layout format:
                </p>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 22px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #0b1326; border: 1.5px solid {{ $pdfExportMode === 'single_pdf' ? '#3b82f6' : '#1c2638' }}; border-radius: 10px; cursor: pointer;">
                        <input type="radio" wire:model.live="pdfExportMode" value="single_pdf" style="margin-top: 3px; accent-color: #3b82f6;" />
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #ffffff;">Single PDF (All Interns Combined)</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Compiles all enrolled interns into a single structured multi-page document.</div>
                        </div>
                    </label>

                    @if ($selectedIntern)
                        <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #0b1326; border: 1.5px solid {{ $pdfExportMode === 'current_intern' ? '#3b82f6' : '#1c2638' }}; border-radius: 10px; cursor: pointer;">
                            <input type="radio" wire:model.live="pdfExportMode" value="current_intern" style="margin-top: 3px; accent-color: #3b82f6;" />
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700; color: #ffffff;">Current Intern PDF ({{ $selectedIntern->name }})</div>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Downloads only the selected intern's 1-page progress evaluation report.</div>
                            </div>
                        </label>
                    @endif

                    <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #0b1326; border: 1.5px solid {{ $pdfExportMode === 'separate_pdfs' ? '#3b82f6' : '#1c2638' }}; border-radius: 10px; cursor: pointer;">
                        <input type="radio" wire:model.live="pdfExportMode" value="separate_pdfs" style="margin-top: 3px; accent-color: #3b82f6;" />
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #ffffff;">Separate PDFs in ZIP Archive</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Generates individual PDF files for each intern packed into a downloadable ZIP.</div>
                        </div>
                    </label>
                </div>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button type="button" wire:click="closePdfModal" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid #334155; padding: 9px 16px; border-radius: 9px; cursor: pointer; font-size: 13px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="button" wire:click="executePdfDownload" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 9px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);">
                        Download PDF Now
                    </button>
            </div>
        </div>
    @endif

    {{-- ── Quick-Edit Daily Timing, Tasks & Attendance Modal ── --}}
    @if ($showTimingModal)
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(11, 19, 38, 0.78); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; padding: 16px;">
            <div style="background-color: #131b2e; border-radius: 16px; border: 1.5px solid #222a3d; width: 100%; max-width: 530px; max-height: calc(100vh - 32px); max-height: calc(100dvh - 32px); display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); overflow: hidden;">
                {{-- Header (Pinned) --}}
                <div style="padding: 16px 22px; border-bottom: 1.5px solid #222a3d; background-color: #171f33; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center;">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 style="font-size: 15.5px; font-weight: 800; color: #ffffff; margin: 0;">
                                Edit Daily Timing, Task & Attendance
                            </h3>
                            <p style="font-size: 11.5px; color: #8e909f; margin: 2px 0 0;">
                                {{ $selectedIntern?->name }} &bull; <strong style="color: #60a5fa;">{{ $timingDateFormatted }}</strong>
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeTimingModal"
                        style="background: transparent; border: none; color: #8e909f; cursor: pointer; padding: 4px; border-radius: 6px; line-height: 1;">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body (Scrollable with custom scrollbar, never overflows screen) --}}
                <div class="modal-scrollable-body" style="padding: 16px 22px; display: flex; flex-direction: column; gap: 14px; overflow-y: auto; flex: 1 1 auto; min-height: 0;">
                    {{-- Shift Schedule Context Ribbon --}}
                    @if ($timingScheduledStart && $timingScheduledEnd)
                        <div style="background-color: #0b1326; border: 1px solid #1c2638; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 11.5px; color: #8e909f;">Official Scheduled Shift:</span>
                            <span style="font-size: 12px; font-weight: 700; color: #60a5fa; font-family: ui-monospace, monospace;">
                                {{ $timingScheduledStart }} — {{ $timingScheduledEnd }}
                            </span>
                        </div>
                    @endif

                    {{-- Attendance Status Selector --}}
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                            Attendance Status
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px;">
                            @php
                                $statusOptions = [
                                    'present' => ['Present', '#10B981', 'rgba(16, 185, 129, 0.15)'],
                                    'late'    => ['Late', '#F59E0B', 'rgba(245, 158, 11, 0.15)'],
                                    'absent'  => ['Absent', '#EF4444', 'rgba(239, 68, 68, 0.15)'],
                                    'wfh'     => ['WFH', '#0284C7', 'rgba(2, 132, 199, 0.15)'],
                                    'leave'   => ['Leave', '#9333EA', 'rgba(147, 51, 234, 0.15)'],
                                    'holiday' => ['Holiday', '#8b5cf6', 'rgba(139, 92, 246, 0.15)'],
                                ];
                            @endphp
                            @foreach ($statusOptions as $sKey => [$sLabel, $sColor, $sBg])
                                <button
                                    type="button"
                                    wire:click="setTimingStatus('{{ $sKey }}')"
                                    style="padding: 7px 8px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; border: 1.5px solid {{ $timingStatus === $sKey ? $sColor : '#1c2638' }}; background-color: {{ $timingStatus === $sKey ? $sBg : '#171f33' }}; color: {{ $timingStatus === $sKey ? $sColor : '#94a3b8' }}; transition: all 0.15s ease;">
                                    {{ $sLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- In-Time & Out-Time Inputs (Displayed when in edit modal) --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd; margin-bottom: 4px;">
                                Starting Time (In-Time)
                            </label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="timingStartTime"
                                placeholder="e.g. 10:15 AM"
                                style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 11px; font-size: 12.5px; font-weight: 600; color: #ffffff; outline: none;"
                            />
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd; margin-bottom: 4px;">
                                Ending Time (Out-Time)
                            </label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="timingEndTime"
                                placeholder="e.g. 04:00 PM"
                                style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 11px; font-size: 12.5px; font-weight: 600; color: #ffffff; outline: none;"
                            />
                        </div>
                    </div>

                    {{-- Dynamic Delay Calculation (Shown when Late) --}}
                    @if ($timingStatus === 'late')
                        <div style="background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                            <span style="font-size: 11.5px; color: #f59e0b; font-weight: 700;">Calculated Late Delay:</span>
                            @if ($timingLateMinutes !== null && $timingLateMinutes > 0)
                                <span style="font-size: 12px; font-weight: 800; color: #fbbf24; background-color: rgba(245, 158, 11, 0.2); padding: 2px 7px; border-radius: 6px;">
                                    ⚠️ Delayed by {{ floor($timingLateMinutes / 60) > 0 ? floor($timingLateMinutes / 60) . 'h ' : '' }}{{ ($timingLateMinutes % 60) }}m ({{ $timingLateMinutes }} mins)
                                </span>
                            @elseif ($timingLateMinutes !== null && $timingLateMinutes === 0)
                                <span style="font-size: 11.5px; font-weight: 700; color: #34d399;">
                                    ✓ On time or earlier than scheduled shift
                                </span>
                            @else
                                <span style="font-size: 11px; color: #8e909f; font-style: italic;">
                                    Enter Starting Time to auto-calculate delay
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Tasks & Deliverables Completed (Daily Logbook Note) --}}
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd;">
                                Tasks & Deliverables Completed (Intern Daily Logbook)
                            </label>
                            <span style="font-size: 10px; color: #60a5fa; font-weight: 700; font-family: ui-monospace, monospace;">
                                🔄 Syncs with Logbook Resource
                            </span>
                        </div>
                        <textarea
                            wire:model="timingTasksCompleted"
                            rows="4"
                            placeholder="Description of tasks completed, features coded, bugs resolved, or daily deliverables..."
                            style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 11px; font-size: 12px; color: #ffffff; outline: none; line-height: 1.5; resize: vertical;"
                        ></textarea>
                    </div>

                    {{-- Hours Worked & Attendance Note in 2 columns --}}
                    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 10px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd; margin-bottom: 4px;">
                                Hours Worked (Logbook)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                max="24"
                                wire:model="timingHoursWorked"
                                placeholder="8.0"
                                style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 11px; font-size: 12px; color: #ffffff; outline: none;"
                            />
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd; margin-bottom: 4px;">
                                Attendance Note / Late Reason
                            </label>
                            <input
                                type="text"
                                wire:model="timingNote"
                                placeholder="e.g. Traffic delay, doctor appointment..."
                                style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 7px 11px; font-size: 12px; color: #ffffff; outline: none;"
                            />
                        </div>
                    </div>

                    {{-- Supervisor Remarks / Feedback (Daily Logbook) --}}
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #dae2fd;">
                                Supervisor Remarks & Feedback (Daily Logbook)
                            </label>
                            <span style="font-size: 10px; color: #34d399; font-weight: 700; font-family: ui-monospace, monospace;">
                                🔄 Syncs with Logbook Resource
                            </span>
                        </div>
                        <textarea
                            wire:model="timingSupervisorRemarks"
                            rows="2"
                            placeholder="Supervisor feedback on this day's work, review notes, guidance, or performance assessment..."
                            style="width: 100%; background-color: #0b1326; border: 1px solid #222a3d; border-radius: 8px; padding: 8px 11px; font-size: 12px; color: #ffffff; outline: none; line-height: 1.4; resize: vertical;"
                        ></textarea>
                        <div style="font-size: 10.5px; color: #64748b; margin-top: 3px;">
                            All modifications made here will automatically synchronize and reflect directly in the Attendance and Daily Logbook modules.
                        </div>
                    </div>
                </div>

                {{-- Footer (Pinned) --}}
                <div style="padding: 12px 22px; background-color: #171f33; border-top: 1.5px solid #222a3d; display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-shrink: 0;">
                    <button
                        type="button"
                        wire:click="closeTimingModal"
                        style="padding: 7px 14px; font-size: 12.5px; font-weight: 600; color: #c4c5d5; background: transparent; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="saveTiming"
                        wire:loading.attr="disabled"
                        style="padding: 7px 18px; font-size: 12.5px; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 8px rgba(30,64,175,0.4); display: inline-flex; align-items: center; gap: 6px;">
                        <span wire:loading.remove wire:target="saveTiming">Confirm & Save All Changes</span>
                        <span wire:loading wire:target="saveTiming">Saving & Syncing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
