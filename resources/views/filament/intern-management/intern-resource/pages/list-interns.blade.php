<x-filament-panels::page>
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

        {{-- 1. Top KPI Statistics Row --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            {{-- Total Interns --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.3);">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Interns</div>
                    <div style="font-size: 28px; font-weight: 700; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['total'] }}</div>
                </div>
            </div>

            {{-- Present Today --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(78, 222, 163, 0.3);">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Present Today</div>
                    <div style="font-size: 28px; font-weight: 700; color: #4edea3; line-height: 1.1; margin-top: 2px;">
                        {{ $stats['present_today'] }}
                    </div>
                </div>
            </div>

            {{-- Pending Tasks --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(147, 51, 234, 0.3);">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Pending Tasks</div>
                    <div style="font-size: 28px; font-weight: 700; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['pending_tasks'] }}</div>
                </div>
            </div>

            {{-- Active Batches --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Active Batches</div>
                    <div style="font-size: 28px; font-weight: 700; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['active_batches'] }}</div>
                </div>
            </div>
        </div>

        {{-- Stitch Cohort Lifecycle View Selector --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap;">
            <div style="display: inline-flex; background-color: #0b1326; border: 1px solid #222a3d; padding: 4px; border-radius: 12px; gap: 4px;">
                <button
                    type="button"
                    wire:click="$set('viewMode', 'current')"
                    style="padding: 7px 16px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'current' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    🟢 Current Active ({{ $activeCount }})
                </button>
                <button
                    type="button"
                    wire:click="$set('viewMode', 'archived')"
                    style="padding: 7px 16px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'archived' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    📁 Archived Cycles ({{ $archivedCount }})
                </button>
                <button
                    type="button"
                    wire:click="$set('viewMode', 'all')"
                    style="padding: 7px 16px; font-size: 13px; font-weight: 700; border-radius: 9px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'all' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 2px 8px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                    All Interns ({{ $totalInternsCount }})
                </button>
            </div>

            @if ($viewMode === 'archived' && !empty($archiveCycles))
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 700; color: #8e909f;">Filter by Cycle:</span>
                    <select
                        wire:model.live="selectedArchiveCycle"
                        style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 14px; font-size: 12px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer;">
                        <option value="">All Past Cycles</option>
                        @foreach ($archiveCycles as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- 2. Search, Batch Filter, Segment Tabs & Actions Toolbar --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            {{-- Search & Controls --}}
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
                {{-- Search Box --}}
                <div style="position: relative; width: 280px; max-width: 100%;">
                    <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8e909f; display: flex; align-items: center; pointer-events: none;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search intern name or email..."
                        style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 9px 12px 9px 38px; font-size: 13px; color: #dae2fd; outline: none; transition: border-color 0.2s;"
                    />
                </div>

                {{-- Batch Dropdown (Placed right after search box) --}}
                <div style="position: relative;">
                    <select
                        wire:model.live="selectedBatchId"
                        style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 9px 32px 9px 14px; font-size: 13px; color: #dae2fd; font-weight: 600; cursor: pointer; outline: none; appearance: none; -webkit-appearance: none;">
                        <option value="">Batch: All Batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}">Batch: {{ $batch->batch_name ?? $batch->name }}</option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #8e909f;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- Segmented Tabs: All, By Batch, By Team --}}
                <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 3px; display: inline-flex; align-items: center; gap: 2px;">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'all')"
                        style="padding: 6px 14px; font-size: 13px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'all' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                        All ({{ $totalInternsCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'batch')"
                        style="padding: 6px 14px; font-size: 13px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'batch' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                        By Batch
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'team')"
                        style="padding: 6px 14px; font-size: 13px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $activeTab === 'team' ? 'background-color: #1e40af; color: #FFFFFF; font-weight: 700;' : 'background-color: transparent; color: #8e909f; font-weight: 500;' }}">
                        By Project
                    </button>
                </div>

                {{-- Select Page Toggle --}}
                @php
                    $isPageAllSelected = !empty($currentPageIds) && empty(array_diff($currentPageIds, $selectedInterns));
                @endphp
                <button
                    type="button"
                    wire:click="selectAllOnPage({{ json_encode($currentPageIds) }})"
                    style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 8px; border: 1px solid #222a3d; background-color: {{ $isPageAllSelected ? 'rgba(30, 64, 175, 0.25)' : '#171f33' }}; color: {{ $isPageAllSelected ? '#b8c4ff' : '#c4c5d5' }}; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                    <input type="checkbox" {{ $isPageAllSelected ? 'checked' : '' }} style="pointer-events: none; accent-color: #3b82f6;" />
                    <span>{{ $isPageAllSelected ? 'Deselect Page' : 'Select Page' }}</span>
                </button>

                {{-- Select All Active Button --}}
                @if ($activeCount > 0)
                    <button
                        type="button"
                        wire:click="selectAllActiveInterns"
                        style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 8px; border: 1px solid #222a3d; background-color: #171f33; color: #c4c5d5; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <span>Select All Active ({{ $activeCount }})</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- 3. Floating Dark Bulk Action Bar (When selected) --}}
        @if (count($selectedInterns) > 0)
            <div style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 12px; padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: #dae2fd; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); margin-top: 14px; margin-bottom: 6px;">
                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                    <span style="background-color: #1e40af; color: #FFFFFF; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ count($selectedInterns) }} Selected
                    </span>
                    <span style="color: #8e909f; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 420px;">
                        {{ $selectedNames }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <button
                        type="button"
                        wire:click="openAssignTaskModal"
                        style="background-color: #006c49; color: #FFFFFF; font-weight: 600; font-size: 12px; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Assign Task
                    </button>

                    <button
                        type="button"
                        wire:click="openAttendanceModal"
                        style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #FFFFFF; font-weight: 600; font-size: 12px; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4); transition: all 0.15s ease;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Mark Attendance
                    </button>

                    <button
                        type="button"
                        wire:click="exportSelected"
                        style="background-color: #222a3d; color: #dae2fd; border: 1px solid #2d3449; font-weight: 500; font-size: 12px; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>

                    <button
                        type="button"
                        wire:click="openArchiveModal"
                        style="background: linear-gradient(135deg, #7C3AED 0%, #6366F1 100%); color: #FFFFFF; font-weight: 700; font-size: 12px; border-radius: 8px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(124, 58, 237, 0.4);">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                        Promote to Completion & Archive
                    </button>

                    <button
                        type="button"
                        wire:click="deselectAll"
                        style="color: #8e909f; background: transparent; border: none; font-size: 12px; font-weight: 500; padding: 6px 10px; cursor: pointer; text-decoration: underline;">
                        Deselect
                    </button>
                </div>
            </div>
        @endif

        {{-- 4. Intern Cards Grid - Separated Clearly by All, By Batch, or By Project --}}
        @if ($interns->count() > 0)

            {{-- 4A: BATCHWISE SEPARATION --}}
            @if ($activeTab === 'batch' && !empty($groupedBatches))
                <div style="display: flex; flex-direction: column; gap: 24px; margin-top: 18px;">
                    @foreach ($groupedBatches as $batchGroup)
                        @if ($batchGroup['is_unassigned'])
                            {{-- Unassigned Batch Container --}}
                            <div style="background-color: rgba(245, 158, 11, 0.05); border: 1.5px dashed rgba(245, 158, 11, 0.35); border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1.5px dashed rgba(245, 158, 11, 0.25);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 44px; height: 44px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                            ⚠️
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <h3 style="font-size: 18px; font-weight: 800; color: #fbbf24; margin: 0;">
                                                    Batch Not Assigned
                                                </h3>
                                                <span style="background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                                    Action Required
                                                </span>
                                            </div>
                                            <div style="font-size: 12px; color: #fde68a; margin-top: 3px;">
                                                {{ $batchGroup['count'] }} {{ \Illuminate\Support\Str::plural('intern', $batchGroup['count']) }} not enrolled into any shift / batch timing yet.
                                            </div>
                                        </div>
                                    </div>
                                    <span style="background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                        {{ $batchGroup['count'] }} Interns
                                    </span>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px;">
                                    @foreach ($batchGroup['interns'] as $intern)
                                        @include('filament.intern-management.partials.intern-directory-card', ['intern' => $intern, 'selectedInterns' => $selectedInterns, 'activeTasks' => $activeTasks, 'activeAssignments' => $activeAssignments])
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Assigned Batch Container --}}
                            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1.5px solid #222a3d;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #171f33; color: #b8c4ff; border: 1px solid #222a3d; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                            🎓
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">
                                                    {{ $batchGroup['name'] }}
                                                </h3>
                                                <span style="background-color: rgba(6, 78, 59, 0.4); color: #6ee7b7; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                    🟢 Active Cohort
                                                </span>
                                                @if ($batchGroup['timing'])
                                                    <span style="background-color: rgba(69, 26, 3, 0.4); color: #fde68a; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3); display: inline-flex; align-items: center; gap: 4px;">
                                                        <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        {{ $batchGroup['timing'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                                                <span>Enrollment: <strong style="color: #ffffff;">{{ $batchGroup['count'] }} / 50 Interns</strong></span>
                                                <span style="color: #475569;">•</span>
                                                <span style="color: #60a5fa; font-weight: 600;">{{ min(100, round(($batchGroup['count'] / 50) * 100)) }}% Allocated</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span style="background-color: #171f33; color: #cbd5e1; border: 1px solid #222a3d; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px;">
                                            {{ $batchGroup['count'] }} {{ \Illuminate\Support\Str::plural('Intern', $batchGroup['count']) }}
                                        </span>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px;">
                                    @foreach ($batchGroup['interns'] as $intern)
                                        @include('filament.intern-management.partials.intern-directory-card', ['intern' => $intern, 'selectedInterns' => $selectedInterns, 'activeTasks' => $activeTasks, 'activeAssignments' => $activeAssignments])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            {{-- 4B: PROJECTWISE SEPARATION --}}
            @elseif ($activeTab === 'team' && !empty($groupedTeams))
                <div style="display: flex; flex-direction: column; gap: 24px; margin-top: 18px;">
                    @foreach ($groupedTeams as $teamGroup)
                        @if ($teamGroup['is_unassigned'])
                            {{-- Unassigned Project Container --}}
                            <div style="background-color: rgba(245, 158, 11, 0.05); border: 1.5px dashed rgba(245, 158, 11, 0.35); border-radius: 16px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1.5px dashed rgba(245, 158, 11, 0.25);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 44px; height: 44px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                            ⚠️
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <h3 style="font-size: 18px; font-weight: 800; color: #fbbf24; margin: 0;">
                                                    Project Not Assigned
                                                </h3>
                                                <span style="background-color: rgba(239, 68, 68, 0.15); color: #ffb4ab; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                                    Action Required
                                                </span>
                                            </div>
                                            <div style="font-size: 12px; color: #fde68a; margin-top: 3px;">
                                                {{ $teamGroup['count'] }} {{ \Illuminate\Support\Str::plural('intern', $teamGroup['count']) }} not assigned to any project squad.
                                            </div>
                                        </div>
                                    </div>
                                    <span style="background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                        {{ $teamGroup['count'] }} Interns
                                    </span>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px;">
                                    @foreach ($teamGroup['interns'] as $intern)
                                        @include('filament.intern-management.partials.intern-directory-card', ['intern' => $intern, 'selectedInterns' => $selectedInterns, 'activeTasks' => $activeTasks, 'activeAssignments' => $activeAssignments])
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Assigned Project Container --}}
                            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1.5px solid #222a3d;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #171f33; color: #d8b4fe; border: 1px solid #222a3d; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                            🚀
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <h3 style="font-size: 18px; font-weight: 800; color: #dae2fd; margin: 0;">
                                                    {{ $teamGroup['name'] }}
                                                </h3>
                                                <span style="background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 6px; border: 1px solid rgba(147, 51, 234, 0.3); text-transform: uppercase;">
                                                    {{ $teamGroup['track'] }}
                                                </span>
                                                <span style="background-color: #171f33; color: #c4c5d5; border: 1px solid #222a3d; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 6px;">
                                                    {{ $teamGroup['squad_badge'] }}
                                                </span>
                                            </div>
                                            <div style="font-size: 12px; color: #8e909f; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                @if ($teamGroup['mentor_name'])
                                                    <span>👤 Mentor: <strong style="color: #dae2fd;">{{ $teamGroup['mentor_name'] }}</strong> ({{ $teamGroup['mentor_title'] ?: 'Project Lead' }})</span>
                                                    <span>•</span>
                                                @endif
                                                <span>Squad: <strong style="color: #dae2fd;">{{ $teamGroup['count'] }} {{ \Illuminate\Support\Str::plural('Member', $teamGroup['count']) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <span style="background-color: #171f33; color: #c4c5d5; border: 1px solid #222a3d; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px;">
                                            {{ $teamGroup['count'] }} {{ \Illuminate\Support\Str::plural('Member', $teamGroup['count']) }}
                                        </span>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px;">
                                    @foreach ($teamGroup['interns'] as $intern)
                                        @include('filament.intern-management.partials.intern-directory-card', ['intern' => $intern, 'selectedInterns' => $selectedInterns, 'activeTasks' => $activeTasks, 'activeAssignments' => $activeAssignments])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            {{-- 4C: ALL INTERNS UNIFIED VIEW --}}
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px; margin-top: 16px;">
                    @foreach ($interns as $intern)
                        @include('filament.intern-management.partials.intern-directory-card', ['intern' => $intern, 'selectedInterns' => $selectedInterns, 'activeTasks' => $activeTasks, 'activeAssignments' => $activeAssignments])
                    @endforeach
                </div>
            @endif

            {{-- 5. Pagination Controls matching Stitch Design --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div style="color: #8e909f; font-size: 13px; font-weight: 500;">
                        Showing <span style="font-weight: 700; color: #dae2fd;">{{ $interns->count() }}</span> of <span style="font-weight: 700; color: #dae2fd;">{{ $interns->total() }}</span> interns
                    </div>

                    {{-- Per Page Selector (10, 20, 50, all) --}}
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 12px; color: #8e909f; font-weight: 600;">Per page:</span>
                        <select
                            wire:model.live="perPage"
                            style="background-color: #171f33; border: 1px solid #222a3d; border-radius: 8px; padding: 4px 10px; font-size: 12px; font-weight: 700; color: #dae2fd; cursor: pointer; outline: none;">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="36">36</option>
                            <option value="-1">All</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 4px;">
                    {{-- Previous Page --}}
                    @if ($interns->onFirstPage())
                        <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #475569; cursor: not-allowed; border-radius: 8px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="previousPage"
                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #8e909f; cursor: pointer; border: none; background: transparent; border-radius: 8px; transition: background-color 0.15s;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach (range(1, $interns->lastPage()) as $page)
                        @if ($page == $interns->currentPage())
                            <span style="background-color: #1e40af; color: #FFFFFF; font-weight: 700; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 13px; box-shadow: 0 2px 6px rgba(30,64,175,0.4);">
                                {{ $page }}
                            </span>
                        @else
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }})"
                                style="color: #c4c5d5; font-weight: 600; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; background: transparent; font-size: 13px; border-radius: 8px; transition: background-color 0.15s;">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach

                    {{-- Next Page --}}
                    @if ($interns->hasMorePages())
                        <button
                            type="button"
                            wire:click="nextPage"
                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #8e909f; cursor: pointer; border: none; background: transparent; border-radius: 8px; transition: background-color 0.15s;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @else
                        <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #475569; cursor: not-allowed; border-radius: 8px;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 48px 24px; text-align: center; margin-top: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background-color: #171f33; color: #8e909f; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border: 1px solid #222a3d;">
                    <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #dae2fd; margin: 0 0 6px;">No interns found</h3>
                <p style="font-size: 13px; color: #8e909f; margin: 0 0 18px;">Try adjusting your search query, batch filter, or active tab.</p>
                <button
                    type="button"
                    wire:click="$set('search', ''); $set('selectedBatchId', null); $set('activeTab', 'all')"
                    style="background-color: #1e40af; color: #FFFFFF; font-weight: 600; font-size: 13px; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer;">
                    Reset Filters
                </button>
            </div>
        @endif

        {{-- Archive / Promote Modal Dialog --}}
        @if ($showArchiveModal)
            <div style="position: fixed; inset: 0; background-color: rgba(11, 19, 38, 0.75); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div style="background-color: #131b2e; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); max-width: 520px; width: 100%; overflow: hidden; border: 1px solid #222a3d;">
                    {{-- Modal Header --}}
                    <div style="background: #171f33; border-bottom: 1px solid #222a3d; padding: 20px 24px; color: #dae2fd; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; border: 1px solid rgba(99, 102, 241, 0.3); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                🎓
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #dae2fd;">Promote to Completion & Archive</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">Move {{ count($selectedInterns) }} selected interns to historical archive</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeArchiveModal" style="background: transparent; border: none; color: #8e909f; cursor: pointer; font-size: 24px; line-height: 1;">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Cycle / Archive Name <span style="color: #ffb4ab;">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="archiveCycleName"
                                placeholder="e.g. 2025-2026 Annual Cohort, Summer 2025 Batch"
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #dae2fd; outline: none; box-sizing: border-box;"
                            />
                            <p style="font-size: 11px; color: #8e909f; margin: 4px 0 0 0;">Selected interns and their project teams will be archived under this exact name.</p>
                        </div>

                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Action Note / Remarks
                            </label>
                            <textarea
                                wire:model="archiveNote"
                                rows="3"
                                placeholder="Describe reason or milestones, e.g.: All tenure requirements fulfilled, final evaluations completed, and certificates issued."
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #dae2fd; outline: none; box-sizing: border-box; resize: vertical;"
                            ></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Official Completion Date
                            </label>
                            <input
                                type="date"
                                wire:model="archiveCompletionDate"
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #dae2fd; outline: none; box-sizing: border-box;"
                            />
                        </div>

                        <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 12px; display: flex; gap: 10px; align-items: flex-start; margin-bottom: 20px;">
                            <span style="font-size: 16px;">ℹ️</span>
                            <p style="font-size: 12px; color: #fbbf24; margin: 0; line-height: 1.4;">
                                Promoting will set these interns to <strong>Completed</strong>, clear them from the current active roll-call, and preserve all attendance records and certificates permanently in the archives.
                            </p>
                        </div>

                        {{-- Modal Actions --}}
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                            <button
                                type="button"
                                wire:click="closeArchiveModal"
                                style="padding: 9px 18px; border-radius: 10px; border: 1px solid #222a3d; background-color: #171f33; color: #c4c5d5; font-size: 13px; font-weight: 600; cursor: pointer;">
                                Cancel
                            </button>
                            <button
                                type="button"
                                wire:click="executeArchival"
                                style="padding: 10px 22px; border-radius: 10px; border: none; background-color: #1e40af; color: #FFFFFF; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);">
                                Confirm Promotion & Move to Archive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Assign Task Pop-up Modal Dialog --}}
        @if ($showAssignTaskModal)
            <div style="position: fixed; inset: 0; background-color: rgba(11, 19, 38, 0.82); backdrop-filter: blur(8px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div style="background-color: #131b2e; border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65); max-width: 580px; width: 100%; overflow: hidden; border: 1.5px solid #222a3d;">
                    {{-- Modal Header --}}
                    <div style="background: #171f33; border-bottom: 1px solid #222a3d; padding: 18px 24px; color: #dae2fd; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                📋
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #dae2fd; letter-spacing: -0.01em;">
                                    Assign Task to Candidates
                                </h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">
                                    Assign an active task or create a new one for <strong>{{ count($selectedInterns) }} selected candidates</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAssignTaskModal" title="Close" style="background: transparent; border: none; color: #8e909f; cursor: pointer; font-size: 24px; line-height: 1; padding: 4px 8px; border-radius: 6px;">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 22px 24px; max-height: calc(85vh - 120px); overflow-y: auto;">
                        {{-- 1. Selected Candidates Capsule Tray --}}
                        <div style="background-color: #0c1424; border: 1px solid #1c2638; border-radius: 12px; padding: 12px 14px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Targeted Candidates ({{ count($selectedInterns) }})
                                </span>
                                <span style="font-size: 10px; font-weight: 700; color: #4edea3; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 6px; border-radius: 4px;">
                                    READY TO RECEIVE
                                </span>
                            </div>

                            <div style="display: flex; flex-wrap: wrap; gap: 6px; max-height: 90px; overflow-y: auto;">
                                @forelse ($selectedInternModels as $sIntern)
                                    @php
                                        $sName = $sIntern->name ?: ($sIntern->offerletter?->name ?? 'Candidate');
                                        $sCode = $sIntern->intern_code ?: ('INT-' . str_pad($sIntern->id, 3, '0', STR_PAD_LEFT));
                                    @endphp
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background-color: #17223b; border: 1px solid #233149; border-radius: 6px; padding: 3px 8px; font-size: 11px; color: #dae2fd;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #3b82f6;"></span>
                                        <strong>{{ $sCode }}</strong>
                                        <span style="color: #94a3b8;">{{ $sName }}</span>
                                    </span>
                                @empty
                                    <span style="font-size: 12px; color: #ffb4ab;">No candidates selected</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- 2. Section: Assign Existing Active Task --}}
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd;">
                                    Select Current Active Task <span style="color: #38bdf8;">*</span>
                                </label>
                                @if ($activeTasks->count() > 0)
                                    <span style="font-size: 11px; color: #8e909f;">{{ $activeTasks->count() }} active tasks available</span>
                                @endif
                            </div>

                            @if ($activeTasks->count() > 0)
                                <div style="position: relative; margin-bottom: 12px;">
                                    <select
                                        wire:model.live="selectedTaskId"
                                        style="width: 100%; background-color: #171f33; border: 1.5px solid #222a3d; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer; box-sizing: border-box;">
                                        <option value="">-- Choose an active task --</option>
                                        @foreach ($activeTasks as $aTask)
                                            <option value="{{ $aTask->task_id }}">
                                                #{{ $aTask->task_id }} • {{ $aTask->title }} (Due: {{ $aTask->due_date ? $aTask->due_date->format('d M Y') : 'Open' }} | Priority: {{ ucfirst($aTask->priority) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Active Task Details Preview Card --}}
                                @if ($selectedTaskModel)
                                    @php
                                        $pCol = match($selectedTaskModel->priority) {
                                            'high'   => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#ffb4ab', 'border' => 'rgba(239, 68, 68, 0.3)'],
                                            'medium' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#fbbf24', 'border' => 'rgba(245, 158, 11, 0.3)'],
                                            default  => ['bg' => 'rgba(59, 130, 246, 0.15)', 'text' => '#93c5fd', 'border' => 'rgba(59, 130, 246, 0.3)'],
                                        };
                                    @endphp
                                    <div style="background-color: #0c1424; border: 1.5px solid rgba(59, 130, 246, 0.35); border-radius: 12px; padding: 14px 16px; margin-bottom: 14px;">
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 6px;">
                                            <div>
                                                <div style="font-size: 15px; font-weight: 800; color: #ffffff;">
                                                    {{ $selectedTaskModel->title }}
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px; flex-wrap: wrap;">
                                                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 7px; border-radius: 4px; background: {{ $pCol['bg'] }}; color: {{ $pCol['text'] }}; border: 1px solid {{ $pCol['border'] }};">
                                                        {{ ucfirst($selectedTaskModel->priority) }} Priority
                                                    </span>
                                                    <span style="font-size: 11px; color: #94a3b8; display: inline-flex; align-items: center; gap: 4px;">
                                                        🗓️ Due: <strong style="color: #dae2fd;">{{ $selectedTaskModel->due_date ? $selectedTaskModel->due_date->format('D, d M Y') : 'Open Ended' }}</strong>
                                                    </span>
                                                    <span style="font-size: 11px; color: #94a3b8;">
                                                        👥 {{ $selectedTaskModel->total_assigned_count }} currently assigned
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if (filled($selectedTaskModel->description))
                                            <p style="font-size: 12px; color: #94a3b8; margin: 8px 0 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $selectedTaskModel->description }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Primary Action Button for Selected Task --}}
                                    <button
                                        type="button"
                                        wire:click="executeAssignTask"
                                        style="width: 100%; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: #FFFFFF; font-weight: 700; font-size: 13px; border-radius: 10px; padding: 11px 16px; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.15s ease;">
                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Assign Task to {{ count($selectedInterns) }} Selected Candidates</span>
                                    </button>
                                @else
                                    <div style="padding: 10px 12px; border-radius: 8px; background-color: #0c1424; border: 1px dashed #222a3d; color: #8e909f; font-size: 12px; text-align: center;">
                                        Select an active task above to review details and confirm assignment.
                                    </div>
                                @endif
                            @else
                                <div style="background-color: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 14px; text-align: center; margin-bottom: 12px;">
                                    <p style="font-size: 12px; color: #fbbf24; margin: 0;">No active tasks found in the system right now.</p>
                                </div>
                            @endif
                        </div>

                        {{-- 3. Visual Divider --}}
                        <div style="display: flex; align-items: center; gap: 12px; margin: 22px 0 16px;">
                            <div style="flex-grow: 1; height: 1px; background-color: #222a3d;"></div>
                            <span style="font-size: 10px; font-weight: 800; color: #8e909f; text-transform: uppercase; letter-spacing: 0.08em;">
                                OR CREATE A NEW DELIVERABLE
                            </span>
                            <div style="flex-grow: 1; height: 1px; background-color: #222a3d;"></div>
                        </div>

                        {{-- 4. Section: Create New Task for Selected Interns --}}
                        <div style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.12) 0%, rgba(15, 23, 42, 0.6) 100%); border: 1.5px dashed rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                    ✨
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: #ffffff;">
                                        Assign a Fresh / New Task
                                    </div>
                                    <p style="font-size: 11px; color: #94a3b8; margin: 2px 0 0; line-height: 1.4;">
                                        Opens the task builder page with all <strong>{{ count($selectedInterns) }} selected candidates preselected</strong> in the audience scope.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="redirectToCreateTaskWithSelected"
                                style="background-color: #1e40af; color: #FFFFFF; font-weight: 700; font-size: 12px; border-radius: 8px; padding: 9px 16px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 3px 10px rgba(30, 64, 175, 0.4); margin-top: 4px; transition: all 0.15s ease;">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>+ Create New Task for These {{ count($selectedInterns) }} Candidates</span>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="background-color: #171f33; border-top: 1px solid #222a3d; padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end;">
                        <button
                            type="button"
                            wire:click="closeAssignTaskModal"
                            style="padding: 8px 18px; border-radius: 8px; border: 1px solid #222a3d; background-color: #131b2e; color: #c4c5d5; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Mark Attendance Pop-up Modal Dialog --}}
        @if ($showAttendanceModal)
            <div style="position: fixed; inset: 0; background-color: rgba(11, 19, 38, 0.85); backdrop-filter: blur(8px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div style="background-color: #131b2e; border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7); max-width: 560px; width: 100%; overflow: hidden; border: 1.5px solid #222a3d;">
                    {{-- Modal Header --}}
                    <div style="background: #171f33; border-bottom: 1px solid #222a3d; padding: 18px 24px; color: #dae2fd; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                📅
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: #dae2fd; letter-spacing: -0.01em;">
                                    Mark Attendance
                                </h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">
                                    Record daily attendance status for <strong>{{ count($selectedInterns) }} selected candidates</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAttendanceModal" title="Close" style="background: transparent; border: none; color: #8e909f; cursor: pointer; font-size: 24px; line-height: 1; padding: 4px 8px; border-radius: 6px;">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 22px 24px; max-height: calc(85vh - 120px); overflow-y: auto;">
                        {{-- 1. Targeted Candidates Capsule Tray --}}
                        <div style="background-color: #0c1424; border: 1px solid #1c2638; border-radius: 12px; padding: 12px 14px; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <span style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Selected Candidates ({{ count($selectedInterns) }})
                                </span>
                                <span style="font-size: 10px; font-weight: 700; color: #60a5fa; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 6px; border-radius: 4px;">
                                    ATTENDANCE TARGET
                                </span>
                            </div>

                            <div style="display: flex; flex-wrap: wrap; gap: 6px; max-height: 80px; overflow-y: auto;">
                                @forelse ($selectedInternModels as $sIntern)
                                    @php
                                        $sName = $sIntern->name ?: ($sIntern->offerletter?->name ?? 'Candidate');
                                        $sCode = $sIntern->intern_code ?: ('INT-' . str_pad($sIntern->id, 3, '0', STR_PAD_LEFT));
                                    @endphp
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background-color: #17223b; border: 1px solid #233149; border-radius: 6px; padding: 3px 8px; font-size: 11px; color: #dae2fd;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #3b82f6;"></span>
                                        <strong>{{ $sCode }}</strong>
                                        <span style="color: #94a3b8;">{{ $sName }}</span>
                                    </span>
                                @empty
                                    <span style="font-size: 12px; color: #ffb4ab;">No candidates selected</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- 2. Date Selection --}}
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd; margin-bottom: 6px;">
                                Attendance Date <span style="color: #38bdf8;">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model.live="attendanceDate"
                                style="width: 100%; background-color: #171f33; border: 1.5px solid #222a3d; border-radius: 10px; padding: 10px 14px; font-size: 13px; font-weight: 600; color: #dae2fd; outline: none; box-sizing: border-box;"
                            />
                        </div>

                        {{-- 3. Status Selection Grid (Present, WFH, Leave, Absent) --}}
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #dae2fd; margin-bottom: 8px;">
                                Select Attendance Status <span style="color: #38bdf8;">*</span>
                            </label>

                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                {{-- Option 1: Present --}}
                                @php $isPres = $selectedAttendanceStatus === 'present'; @endphp
                                <div
                                    wire:click="$set('selectedAttendanceStatus', 'present')"
                                    style="padding: 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 12px; {{ $isPres ? 'background-color: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; box-shadow: 0 0 12px rgba(16, 185, 129, 0.25);' : 'background-color: #171f33; border: 1.5px solid #222a3d;' }}">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background-color: {{ $isPres ? '#10b981' : '#222a3d' }}; color: {{ $isPres ? '#ffffff' : '#34d399' }}; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0;">
                                        ✓
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 800; color: {{ $isPres ? '#ffffff' : '#dae2fd' }};">
                                            Present
                                        </div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                            In-office attendance
                                        </div>
                                    </div>
                                </div>

                                {{-- Option 2: WFH --}}
                                @php $isWfh = $selectedAttendanceStatus === 'wfh'; @endphp
                                <div
                                    wire:click="$set('selectedAttendanceStatus', 'wfh')"
                                    style="padding: 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 12px; {{ $isWfh ? 'background-color: rgba(59, 130, 246, 0.15); border: 2px solid #3b82f6; box-shadow: 0 0 12px rgba(59, 130, 246, 0.25);' : 'background-color: #171f33; border: 1.5px solid #222a3d;' }}">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background-color: {{ $isWfh ? '#3b82f6' : '#222a3d' }}; color: {{ $isWfh ? '#ffffff' : '#60a5fa' }}; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                        🏠
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 800; color: {{ $isWfh ? '#ffffff' : '#dae2fd' }};">
                                            WFH
                                        </div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                            Work from home
                                        </div>
                                    </div>
                                </div>

                                {{-- Option 3: Leave --}}
                                @php $isLeave = $selectedAttendanceStatus === 'leave'; @endphp
                                <div
                                    wire:click="$set('selectedAttendanceStatus', 'leave')"
                                    style="padding: 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 12px; {{ $isLeave ? 'background-color: rgba(245, 158, 11, 0.15); border: 2px solid #fbbf24; box-shadow: 0 0 12px rgba(245, 158, 11, 0.25);' : 'background-color: #171f33; border: 1.5px solid #222a3d;' }}">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background-color: {{ $isLeave ? '#fbbf24' : '#222a3d' }}; color: {{ $isLeave ? '#1c1917' : '#fbbf24' }}; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                        🌴
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 800; color: {{ $isLeave ? '#ffffff' : '#dae2fd' }};">
                                            Leave
                                        </div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                            Approved leave
                                        </div>
                                    </div>
                                </div>

                                {{-- Option 4: Absent --}}
                                @php $isAbsent = $selectedAttendanceStatus === 'absent'; @endphp
                                <div
                                    wire:click="$set('selectedAttendanceStatus', 'absent')"
                                    style="padding: 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 12px; {{ $isAbsent ? 'background-color: rgba(239, 68, 68, 0.15); border: 2px solid #ef4444; box-shadow: 0 0 12px rgba(239, 68, 68, 0.25);' : 'background-color: #171f33; border: 1.5px solid #222a3d;' }}">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background-color: {{ $isAbsent ? '#ef4444' : '#222a3d' }}; color: {{ $isAbsent ? '#ffffff' : '#f87171' }}; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0;">
                                        ✕
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 800; color: {{ $isAbsent ? '#ffffff' : '#dae2fd' }};">
                                            Absent
                                        </div>
                                        <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                            Unexcused absence
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Optional Remarks / Note --}}
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #8e909f; margin-bottom: 6px;">
                                Optional Note / Remarks
                            </label>
                            <input
                                type="text"
                                wire:model="attendanceNote"
                                placeholder="e.g. Approved medical leave, remote project sprint, client visit..."
                                style="width: 100%; background-color: #171f33; border: 1px solid #222a3d; border-radius: 10px; padding: 9px 14px; font-size: 13px; color: #dae2fd; outline: none; box-sizing: border-box;"
                            />
                        </div>

                        {{-- Confirmation Action Button --}}
                        @php
                            $btnStyle = match($selectedAttendanceStatus) {
                                'present' => 'background: linear-gradient(135deg, #10B981 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);',
                                'wfh'     => 'background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);',
                                'leave'   => 'background: linear-gradient(135deg, #d97706 0%, #b45309 100%); box-shadow: 0 4px 14px rgba(217, 119, 6, 0.4);',
                                'absent'  => 'background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);',
                                default   => 'background: linear-gradient(135deg, #10B981 0%, #059669 100%);',
                            };
                            $labelStatus = match($selectedAttendanceStatus) {
                                'wfh' => 'WFH',
                                default => ucfirst($selectedAttendanceStatus),
                            };
                        @endphp
                        <button
                            type="button"
                            wire:click="executeMarkAttendance"
                            style="width: 100%; {{ $btnStyle }} color: #FFFFFF; font-weight: 700; font-size: 13px; border-radius: 10px; padding: 11px 16px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.15s ease;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Mark as {{ $labelStatus }} for {{ count($selectedInterns) }} {{ \Illuminate\Support\Str::plural('Candidate', count($selectedInterns)) }}</span>
                        </button>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="background-color: #171f33; border-top: 1px solid #222a3d; padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end;">
                        <button
                            type="button"
                            wire:click="closeAttendanceModal"
                            style="padding: 8px 18px; border-radius: 8px; border: 1px solid #222a3d; background-color: #131b2e; color: #c4c5d5; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
