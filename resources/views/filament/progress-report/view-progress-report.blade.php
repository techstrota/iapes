<x-filament-panels::page>
@php
    $report = $this->record;
    $weekStart = \Carbon\Carbon::parse($report->week_start_date);
    $weekEnd = \Carbon\Carbon::parse($report->week_end_date);
    $formattedRange = $weekStart->format('d M Y') . ' — ' . $weekEnd->format('d M Y');

    // All report interns collection
    $allReportInterns = $report->reportInterns()
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

    // Tally attendance summary counts
    $attendanceCounts = [
        'present'  => 0,
        'wfh'      => 0,
        'leave'    => 0,
        'absent'   => 0,
        'holiday'  => 0,
        'week_off' => 0,
        'unmarked' => 0,
    ];

    $weekRows = [];
    foreach ($weekDays as $dayCarbon) {
        $dateStr = $dayCarbon->toDateString();
        $att = $attendances->get($dateStr);
        $hol = $holidays->get($dateStr);
        $log = $dailyLogbooks->first(fn ($l) => \Carbon\Carbon::parse($l->date)->toDateString() === $dateStr);

        $status = 'unmarked';
        $note = null;

        if ($att) {
            $status = strtolower($att->status);
            $note = $att->note;
        } elseif ($hol) {
            $status = 'holiday';
            $note = $hol->note;
        } elseif ($dayCarbon->isSunday()) {
            $status = 'week_off';
        }

        if (isset($attendanceCounts[$status])) {
            $attendanceCounts[$status]++;
        } else {
            $attendanceCounts['unmarked']++;
        }

        $weekRows[] = [
            'carbon'   => $dayCarbon,
            'date_str' => $dateStr,
            'status'   => $status,
            'note'     => $note,
            'log'      => $log,
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

                {{-- Section 1: Daily Attendance & Tasks Filled by Intern (Table Format) --}}
                <div class="pr-card" style="padding: 22px 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #222a3d;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">
                                    Daily Tasks & Attendance Log
                                </h3>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">
                                    Full date-by-date breakdown of attendance and logged tasks for {{ $selectedIntern->name }} ({{ $formattedRange }})
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35);">
                                📝 {{ $dailyLogbooks->count() }}/{{ count($weekDays) }} Days Logged
                            </span>
                            <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                ⏱️ {{ $totalLoggedHours }} Total Hours
                            </span>
                        </div>
                    </div>

                    {{-- Comprehensive Table for All Dates in Duration --}}
                    <div style="overflow-x: auto; border: 1.5px solid #1c2638; border-radius: 12px; background-color: #0b1326; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);">
                        <table class="pr-table">
                            <thead>
                                <tr>
                                    <th style="width: 135px;">Date & Day</th>
                                    <th style="width: 130px;">Attendance</th>
                                    <th>Tasks & Deliverables Completed (Intern Log)</th>
                                    <th style="width: 105px; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weekRows as $row)
                                    @php
                                        $d = $row['carbon'];
                                        $dStr = $row['date_str'];
                                        $st = $row['status'];
                                        $attNote = $row['note'];
                                        $log = $row['log'];
                                        $isToday = $d->isToday();
                                    @endphp
                                    <tr style="{{ $st === 'week_off' ? 'background-color: rgba(139, 92, 246, 0.02);' : ($st === 'holiday' ? 'background-color: rgba(245, 158, 11, 0.02);' : '') }}">
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

                                        {{-- 2. Attendance Status Badge --}}
                                        <td>
                                            @if ($st === 'present')
                                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                                    ● Present
                                                </span>
                                            @elseif ($st === 'wfh')
                                                <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35);">
                                                    🏠 WFH
                                                </span>
                                            @elseif ($st === 'leave')
                                                <span class="pr-badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);">
                                                    🌴 On Leave
                                                </span>
                                            @elseif ($st === 'absent')
                                                <span class="pr-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35);">
                                                    ✕ Absent
                                                </span>
                                            @elseif ($st === 'holiday')
                                                <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);">
                                                    🎉 Holiday
                                                </span>
                                                @if ($attNote)
                                                    <div style="font-size: 10.5px; color: #fbbf24; margin-top: 3px; font-style: italic;">{{ $attNote }}</div>
                                                @endif
                                            @elseif ($st === 'week_off')
                                                <span class="pr-badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.35);">
                                                    ☕ Sunday Off
                                                </span>
                                            @else
                                                <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                                    — Not Marked
                                                </span>
                                            @endif
                                        </td>

                                        {{-- 3. Tasks & Deliverables Completed (Intern Log) --}}
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
                                            @else
                                                @if ($st === 'holiday')
                                                    <div style="color: #fbbf24; font-size: 12px; font-style: italic;">
                                                        🎉 Official Holiday — No work scheduled
                                                    </div>
                                                @elseif ($st === 'week_off')
                                                    <div style="color: #a78bfa; font-size: 12px; font-style: italic;">
                                                        ☕ Scheduled Sunday Week-Off
                                                    </div>
                                                @elseif ($st === 'leave')
                                                    <div style="color: #c084fc; font-size: 12px; font-style: italic;">
                                                        🌴 Intern on Approved Leave
                                                    </div>
                                                @elseif ($st === 'absent')
                                                    <div style="color: #f87171; font-size: 12px; font-style: italic;">
                                                        ✕ Intern Marked Absent
                                                    </div>
                                                @else
                                                    <div style="color: #64748b; font-size: 12px; font-style: italic;">
                                                        No logbook submitted
                                                    </div>
                                                @endif
                                            @endif
                                        </td>

                                        {{-- 6. Status --}}
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
                                            @else
                                                <span class="pr-badge" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 10.5px;">
                                                    Missing
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #080e1d; border-top: 1.5px solid #222a3d; font-size: 12px;">
                                    <td colspan="4" style="padding: 12px 16px;">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span style="font-weight: 700; color: #ffffff; font-size: 12px;">Attendance Breakdown:</span>
                                            @if ($attendanceCounts['present'] > 0)
                                                <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">{{ $attendanceCounts['present'] }} Present</span>
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
                                                <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">{{ $attendanceCounts['holiday'] }} Holiday</span>
                                            @endif
                                            @if ($attendanceCounts['week_off'] > 0)
                                                <span class="pr-badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3);">{{ $attendanceCounts['week_off'] }} Sunday Off</span>
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
            <div class="pr-card" style="padding: 40px; text-align: center; color: #8e909f;">
                Select an intern from the left list to review their daily tasks and write weekly evaluations.
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
        </div>
    @endif

</div>
</x-filament-panels::page>
