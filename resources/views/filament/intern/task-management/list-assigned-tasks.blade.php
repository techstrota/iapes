<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
@php
    $stats = $this->getTaskStats();
@endphp

<style>
    /* ── Stitch Tabs Toolbar Styling ── */
    .fi-resource-list-records-page .fi-tabs {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 14px !important;
        padding: 6px !important;
        gap: 6px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
    }
    .fi-resource-list-records-page .fi-tabs-item {
        border-radius: 10px !important;
        padding: 8px 16px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #8e909f !important;
        transition: all 0.15s ease !important;
        border: none !important;
        background-color: transparent !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    .fi-resource-list-records-page .fi-tabs-item:hover {
        background-color: #171f33 !important;
        color: #dae2fd !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] {
        background-color: #1e40af !important;
        color: #ffffff !important;
        border: 1px solid #3b82f6 !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        font-weight: 700 !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active svg,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] svg,
    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-tabs-item-icon,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] .fi-tabs-item-icon {
        color: #ffffff !important;
    }
    .fi-resource-list-records-page .fi-tabs-item .fi-badge {
        font-size: 11px !important;
        font-weight: 700 !important;
        border-radius: 9999px !important;
        padding: 2px 7px !important;
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #dae2fd !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-badge,
    .fi-resource-list-records-page .fi-tabs-item[aria-selected="true"] .fi-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
    }

    /* ── Stitch Table Container & Header ── */
    .fi-resource-list-records-page .fi-ta-ctn {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden !important;
    }
    .fi-resource-list-records-page .fi-ta-header-ctn,
    .fi-resource-list-records-page .fi-ta-header {
        background-color: #131b2e !important;
        border-bottom: 1.5px solid #1c2638 !important;
        padding: 14px 20px !important;
    }

    /* Search Field */
    .fi-resource-list-records-page .fi-ta-search-field .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4) !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field input {
        color: #dae2fd !important;
        font-size: 13px !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field input::placeholder {
        color: #8e909f !important;
    }
    .fi-resource-list-records-page .fi-ta-search-field .fi-input-wrp:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
    }

    /* Filters trigger button */
    .fi-resource-list-records-page .fi-ta-filters-trigger-btn,
    .fi-resource-list-records-page .fi-ta-filter-trigger-action button,
    .fi-resource-list-records-page .fi-ta-filter-trigger button {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        transition: all 0.15s ease !important;
    }
    .fi-resource-list-records-page .fi-ta-filters-trigger-btn:hover,
    .fi-resource-list-records-page .fi-ta-filter-trigger-action button:hover {
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }

    /* Content & Background */
    .fi-resource-list-records-page .fi-ta-content {
        background-color: #0b1326 !important;
        padding: 16px !important;
    }

    /* Hide duplicate Filament row actions below cards in content grid */
    .fi-resource-list-records-page .fi-ta-content-grid .fi-ta-actions,
    .fi-resource-list-records-page .fi-ta-content-grid .fi-ta-actions-cell {
        display: none !important;
    }

    /* Empty State */
    .fi-resource-list-records-page .fi-ta-empty-state {
        background-color: #131b2e !important;
        border-radius: 16px !important;
        padding: 50px 24px !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-icon-ctn {
        background-color: rgba(99, 102, 241, 0.15) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        color: #b8c4ff !important;
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 16px auto !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-icon-ctn svg {
        width: 28px !important;
        height: 28px !important;
        color: #b8c4ff !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-heading {
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        margin-bottom: 6px !important;
    }
    .fi-resource-list-records-page .fi-ta-empty-state-description {
        color: #8e909f !important;
        font-size: 13px !important;
        max-width: 440px !important;
        margin: 0 auto 18px auto !important;
        line-height: 1.5 !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

    {{-- 1. Hero Context & Action Banner --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; position: relative; z-index: 1;">
            {{-- Left: Identity & Info --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                            My Assigned Tasks & Deliverables
                        </h1>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(99, 102, 241, 0.3);">
                            {{ $stats['total_tasks'] }} Assigned Milestones
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Review specifications, complete deliverables, upload repository links, and track mentor evaluations and grades.
                    </p>
                </div>
            </div>

            {{-- Right: Status summary badge --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @if ($stats['pending_action'] > 0)
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $stats['pending_action'] }} Tasks Awaiting Submission</span>
                    </div>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 7px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.35); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;">
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>All Tasks Submitted</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Top 4 Stitch KPI Statistics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        {{-- Card 1: Action Required --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Action Required</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: {{ $stats['pending_action'] > 0 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(99, 102, 241, 0.15)' }}; color: {{ $stats['pending_action'] > 0 ? '#fbbf24' : '#b8c4ff' }}; display: flex; align-items: center; justify-content: center; border: 1px solid {{ $stats['pending_action'] > 0 ? 'rgba(245, 158, 11, 0.3)' : 'rgba(99, 102, 241, 0.3)' }};">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: {{ $stats['pending_action'] > 0 ? '#fbbf24' : '#ffffff' }}; line-height: 1.1;">
                {{ $stats['pending_action'] }}
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                @if ($stats['overdue_count'] > 0)
                    <span style="color: #f87171; font-weight: 700;">⚠️ {{ $stats['overdue_count'] }} Overdue</span>
                @else
                    <span style="color: #93c5fd; font-weight: 600;">Due dates on schedule</span>
                @endif
                <span>Pending Deliverable</span>
            </div>
        </div>

        {{-- Card 2: In Review --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Under Evaluation</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #60a5fa; line-height: 1.1;">
                {{ $stats['in_review'] }}
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #93c5fd; font-weight: 600;">Work submitted</span>
                <span>Mentor Reviewing</span>
            </div>
        </div>

        {{-- Card 3: Approved & Graded --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Approved & Graded</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #4edea3; line-height: 1.1;">
                {{ $stats['approved_count'] }}
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #4edea3; font-weight: 700;">{{ $stats['completion_rate'] }}% Completed</span>
                <span>Passed Criteria</span>
            </div>
        </div>

        {{-- Card 4: Average Score --}}
        <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Average Evaluation</span>
                <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #c0c1ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(192, 193, 255, 0.3);">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
            </div>
            <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                {{ $stats['avg_score'] > 0 ? $stats['avg_score'] : '—' }}<span style="font-size: 16px; font-weight: 500; color: #64748b;">{{ $stats['avg_score'] > 0 ? '/100' : '' }}</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                <span style="color: #c0c1ff; font-weight: 600;">Cohort Standard</span>
                <span>Graded Work</span>
            </div>
        </div>
    </div>

    {{-- 3. Segmented Tabs & Records Table --}}
    <div class="flex flex-col gap-y-4">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>

</div>
</x-filament-panels::page>
