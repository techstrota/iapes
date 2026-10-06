<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
@php
    $indexUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('index');
    $editUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('edit', ['record' => $record->task_id]);
    $evalUrl = route('filament.admin.resources.task-management.task-submission-and-evaluations.index');

    $priority = strtolower($record->priority ?? 'medium');
    $status = $record->status ?? 'active';
    $dueDate = $record->due_date ? \Carbon\Carbon::parse($record->due_date) : null;
    $isOverdue = $record->is_overdue;

    $totalAssigned = $record->total_assigned_count;
    $submittedCount = $record->submitted_count;
    $pendingCount = $record->pending_count;
    $evaluatedCount = $record->evaluated_count;

    $pendingEvalCount = $record->submissions()->whereNull('evaluated_at')->count();
    $assignedInterns = $record->assigned_interns;

    $relationManagers = $this->getRelationManagers();
    $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
@endphp

<style>
    /* ── Stitch View Task Styling ── */
    .fi-resource-view-record-page .fi-infolist .fi-section,
    .fi-resource-view-record-page .fi-section {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }
    .fi-resource-view-record-page .fi-section-header {
        background-color: #0b1326 !important;
        border-bottom: 1.5px solid #222a3d !important;
        padding: 14px 20px !important;
    }
    .fi-resource-view-record-page .fi-section-header-heading {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
    }
    .fi-resource-view-record-page .fi-section-content {
        padding: 22px !important;
        background-color: #131b2e !important;
    }

    .fi-resource-view-record-page .fi-in-entry-label {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #8e909f !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 4px !important;
    }
    .fi-resource-view-record-page .fi-in-text {
        color: #dae2fd !important;
    }

    /* Relation Managers Tabs & Tables */
    .fi-resource-view-record-page .fi-ta-ctn {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden !important;
    }
    .fi-resource-view-record-page .fi-ta-header-ctn,
    .fi-resource-view-record-page .fi-ta-header {
        background-color: #131b2e !important;
        border-bottom: 1.5px solid #1c2638 !important;
        padding: 14px 20px !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

    {{-- 1. Hero Context & Action Banner --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        {{-- Breadcrumb Navigation Link --}}
        <div style="margin-bottom: 14px; position: relative; z-index: 1;">
            <a href="{{ $indexUrl }}"
               style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #8e909f; text-decoration: none; transition: color 0.15s ease;"
               onmouseover="this.style.color='#b8c4ff';"
               onmouseout="this.style.color='#8e909f';">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Tasks Directory</span>
            </a>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; position: relative; z-index: 1;">
            {{-- Left: Task Title & Badges --}}
            <div style="display: flex; align-items: flex-start; gap: 16px; max-width: 65%;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15); margin-top: 2px;">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <h1 style="font-size: 23px; font-weight: 800; color: #ffffff; margin: 0 0 8px 0; line-height: 1.25;">
                        {{ $record->title }}
                    </h1>
                    
                    {{-- Badges Row --}}
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        {{-- Status Badge --}}
                        @if ($status === 'completed')
                            <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 9px; border-radius: 9999px;">
                                ✓ Completed
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 3px 9px; border-radius: 9999px;">
                                ● Active Milestone
                            </span>
                        @endif

                        {{-- Priority Badge --}}
                        @if ($priority === 'high')
                            <span style="font-size: 11px; font-weight: 700; color: #fca5a5; background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); padding: 3px 9px; border-radius: 9999px;">
                                High Priority
                            </span>
                        @elseif ($priority === 'medium')
                            <span style="font-size: 11px; font-weight: 700; color: #fcd34d; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 3px 9px; border-radius: 9999px;">
                                Medium Priority
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; color: #6ee7b7; background-color: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); padding: 3px 9px; border-radius: 9999px;">
                                Low Priority
                            </span>
                        @endif

                        {{-- Assignment Scope Badge --}}
                        <span style="font-size: 11px; font-weight: 600; color: #c4c5d5; background-color: #090e1c; border: 1px solid #222a3d; padding: 3px 9px; border-radius: 9999px;">
                            {{ $totalAssigned === 1 ? 'Solo Task' : 'Group Task (' . $totalAssigned . ' Interns)' }}
                        </span>

                        {{-- Deadline / Due Date Badge --}}
                        @if ($dueDate)
                            <span style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 9999px; {{ $isOverdue ? 'color: #fca5a5; background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);' : 'color: #cbd5e1; background-color: #090e1c; border: 1px solid #222a3d;' }}">
                                @if ($isOverdue)
                                    Overdue • {{ $dueDate->format('M d, Y') }} ({{ $dueDate->diffForHumans() }})
                                @else
                                    Due: {{ $dueDate->format('M d, Y') }} ({{ $dueDate->diffForHumans() }})
                                @endif
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 600; color: #8e909f; background-color: #090e1c; border: 1px solid #222a3d; padding: 3px 9px; border-radius: 9999px;">
                                No Deadline Set
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Actions Toolbar --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                {{-- Edit Task Button (Clean Secondary) --}}
                <a href="{{ $editUrl }}"
                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #171f33; color: #dae2fd; padding: 9px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;"
                   onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
                   onmouseout="this.style.borderColor='#222a3d'; this.style.color='#dae2fd';">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Task
                </a>

                {{-- Mark as Completed Button (Primary Blue) --}}
                @if ($status !== 'completed')
                    <button
                        type="button"
                        wire:click="markTaskCompleted"
                        wire:confirm="Are you sure you want to mark this task as completed?"
                        style="display: inline-flex; align-items: center; gap: 6px; background-color: #1e40af; color: #ffffff; padding: 9px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 700; border: 1px solid #3b82f6; box-shadow: 0 2px 10px rgba(30, 64, 175, 0.4); cursor: pointer; transition: all 0.15s ease;"
                        onmouseover="this.style.backgroundColor='#1d4ed8';"
                        onmouseout="this.style.backgroundColor='#1e40af';">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark as Completed
                    </button>
                @endif

                {{-- Evaluate Deliverables Link --}}
                <a href="{{ $evalUrl }}"
                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #171f33; color: #dae2fd; padding: 9px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;"
                   onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#ffffff';"
                   onmouseout="this.style.borderColor='#222a3d'; this.style.color='#dae2fd';">
                    <svg style="width: 15px; height: 15px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Evaluate ({{ $pendingEvalCount }} Pending)
                </a>
            </div>
        </div>
    </div>

    {{-- 2. 4 Stitch KPI Statistics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px;">
        {{-- Card 1: Total Assigned --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Assigned</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $totalAssigned }}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #93c5fd; font-weight: 600;">Active Cohort Members</span>
                <span>Assigned Target</span>
            </div>
        </div>

        {{-- Card 2: Submitted --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Submitted</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $submittedCount }}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #60a5fa; font-weight: 600;">
                    {{ $totalAssigned > 0 ? round(($submittedCount / $totalAssigned) * 100) : 0 }}% Turnout Rate
                </span>
                <span>Deliverables In</span>
            </div>
        </div>

        {{-- Card 3: Pending --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Pending Submissions</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(245, 158, 11, 0.15); color: #fcd34d; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $pendingCount }}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #fbbf24; font-weight: 600;">Awaiting Work</span>
                <span>{{ $pendingCount === 0 ? 'All Turned In' : 'Interns Remaining' }}</span>
            </div>
        </div>

        {{-- Card 4: Evaluated --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Evaluated & Graded</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #6ee7b7; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $evaluatedCount }}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #4edea3; font-weight: 600;">
                    {{ $submittedCount > 0 ? round(($evaluatedCount / $submittedCount) * 100) : 0 }}% Processed
                </span>
                <span>{{ $pendingEvalCount }} Pending Review</span>
            </div>
        </div>
    </div>

    {{-- 3. Task Overview Specifications Infolist --}}
    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        @if ($this->hasInfolist())
            {{ $this->infolist }}
        @else
            <div wire:key="{{ $this->getId() }}.forms.{{ $this->getFormStatePath() }}">
                {{ $this->form }}
            </div>
        @endif
    @endif

    {{-- 4. Submitted Deliverables Relation Manager --}}
    @if (count($relationManagers))
        <div style="margin-top: 24px;">
            <x-filament-panels::resources.relation-managers
                :active-locale="isset($activeLocale) ? $activeLocale : null"
                :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
                :content-tab-label="$this->getContentTabLabel()"
                :content-tab-icon="$this->getContentTabIcon()"
                :content-tab-position="$this->getContentTabPosition()"
                :managers="$relationManagers"
                :owner-record="$record"
                :page-class="static::class"
            >
                @if ($hasCombinedRelationManagerTabsWithContent)
                    <x-slot name="content">
                        @if ($this->hasInfolist())
                            {{ $this->infolist }}
                        @else
                            {{ $this->form }}
                        @endif
                    </x-slot>
                @endif
            </x-filament-panels::resources.relation-managers>
        </div>
    @endif

</div>
</x-filament-panels::page>
