<x-filament-panels::page>
@php
    $createUrl = \App\Filament\Resources\CollegeResource::getUrl('create');
@endphp

<style>
    /* ── Stitch Dark Theme Colleges Directory ── */
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
        white-space: nowrap;
    }

    .pr-report-card {
        background-color: #131b2e;
        border: 1.5px solid #222a3d;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .pr-report-card:hover {
        transform: translateY(-3px);
        border-color: #3b82f6;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 16px rgba(59, 130, 246, 0.2);
    }

    .pr-report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #1e40af, #3b82f6);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .pr-report-card:hover::before {
        opacity: 1;
    }

    .pr-search-input {
        width: 100%;
        background-color: #0b1326;
        border: 1px solid #222a3d;
        border-radius: 10px;
        padding: 8px 14px 8px 36px;
        font-size: 13px;
        color: #dae2fd;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }

    .pr-search-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }

    .pr-filter-btn {
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pr-btn-secondary {
        background-color: #172238;
        color: #93c5fd;
        border: 1px solid #25334d;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 13px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .pr-btn-secondary:hover {
        background-color: #1e2d4a;
        border-color: #3b82f6;
        color: #ffffff;
    }

    .pr-btn-primary {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: #ffffff;
        border: none;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 15px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4);
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .pr-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.55);
    }

    .pr-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #222a3d;
        background-color: #0b1326;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pr-icon-btn:hover {
        border-color: #3b82f6;
        color: #ffffff;
        background-color: #172238;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 20px;">

    {{-- ── 1. Top Hero Context & Action Banner ── --}}
    <div class="pr-card" style="padding: 22px 26px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
            {{-- Left Title & Info --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            Colleges & Universities
                        </h1>
                        <span class="pr-badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35);">
                            {{ $stats['total_colleges'] }} Registered Institutions
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Manage registered colleges, create internship batches, track active interns, and generate customized evaluation reports.
                    </p>
                </div>
            </div>

            {{-- Right Quick Actions --}}
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ $createUrl }}"
                   style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; border: none; font-size: 13px; font-weight: 700; padding: 10px 20px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 16px rgba(217, 119, 6, 0.4); transition: transform 0.15s ease;"
                   onmouseover="this.style.transform='scale(1.02)';"
                   onmouseout="this.style.transform='scale(1)';"
                   title="Register a new college / institution">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Register New College</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Top 4 KPI Statistics Cards ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        {{-- Card 1: Total Colleges --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Colleges</div>
                <div style="font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.1; margin-top: 2px;">{{ $stats['total_colleges'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Registered Institutions</div>
            </div>
        </div>

        {{-- Card 2: Total Batches --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Batches</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; line-height: 1.1; margin-top: 2px;">{{ $stats['total_batches'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">
                    Active Cohorts @if (!empty($stats['archived_batches'])) &bull; <span style="color: #fbbf24;">{{ $stats['archived_batches'] }} Archived</span> @endif
                </div>
            </div>
        </div>

        {{-- Card 3: Active Interns --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Active Interns</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; line-height: 1.1; margin-top: 2px;">{{ $stats['active_interns'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Currently Enrolled Interns</div>
            </div>
        </div>

        {{-- Card 4: Affiliated Universities --}}
        <div class="pr-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(59, 130, 246, 0.3);">
                <svg style="width: 23px; height: 23px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Universities</div>
                <div style="font-size: 26px; font-weight: 800; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['unique_universities'] }}</div>
                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 3px;">Affiliated University Boards</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Search & University Filter Toolbar ── --}}
    <div class="pr-card" style="padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        {{-- Left: University Filter Dropdown --}}
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">University:</span>
                <select
                    wire:model.live="universityFilter"
                    style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 12px; font-size: 12.5px; font-weight: 600; color: #dae2fd; outline: none; cursor: pointer;">
                    <option value="all">🏛️ All Universities</option>
                    @foreach ($universities as $univ)
                        <option value="{{ $univ }}">{{ $univ }}</option>
                    @endforeach
                    <option value="autonomous">🏢 Autonomous / Direct Campuses</option>
                </select>
            </div>
        </div>

        {{-- Right: Search & Per Page --}}
        <div style="display: flex; align-items: center; gap: 12px; flex-grow: 1; max-width: 460px; min-width: 260px;">
            <div style="position: relative; flex-grow: 1;">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search college name, university, city, or coordinator..."
                    class="pr-search-input"
                />
                <svg style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <select
                wire:model.live="perPage"
                style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 7px 10px; font-size: 12px; font-weight: 600; color: #8e909f; outline: none; cursor: pointer;">
                <option value="6">6 / page</option>
                <option value="12">12 / page</option>
                <option value="24">24 / page</option>
            </select>
        </div>
    </div>

    {{-- ── 4. Colleges Displayed in CARD STYLE (Matching Progress Reports UI) ── --}}
    @if ($colleges->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px;">
            @foreach ($colleges as $college)
                @php
                    $viewUrl = \App\Filament\Resources\CollegeResource::getUrl('view', ['record' => $college]);
                    $editUrl = \App\Filament\Resources\CollegeResource::getUrl('edit', ['record' => $college]);
                    $createBatchUrl = \App\Filament\Resources\CollegeEvaluationResource::getUrl('create', ['college_id' => $college->id]);

                    $batchesCount = $college->active_evaluations_count ?? ($college->evaluations_count ?? 0);
                    $archivedBatchesCount = $college->archived_evaluations_count ?? 0;
                    $internsCount = $college->interns_count ?? 0;
                    $activeInternsCount = $college->active_interns_count ?? 0;

                    $words = preg_split('/\s+/', trim($college->name));
                    $initials = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
                    if (empty($initials)) $initials = 'CL';

                    $address = $college->address ?? '';
                @endphp

                <div
                    class="pr-report-card"
                    onclick="window.location.href='{{ $viewUrl }}'"
                    title="Click to view batches & evaluation reports for {{ $college->name }}">

                    {{-- ── Card Top Header Row ── --}}
                    <div>
                        {{-- Badges Row --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                @if ($college->university_name)
                                    <span class="pr-badge" style="background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35);" title="Affiliated University: {{ $college->university_name }}">
                                        🏛️ {{ \Illuminate\Support\Str::limit($college->university_name, 22) }}
                                    </span>
                                @else
                                    <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25);">
                                        🏢 Autonomous
                                    </span>
                                @endif

                                @if ($address)
                                    <span class="pr-badge" style="background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);" title="Address: {{ $address }}">
                                        📍 {{ \Illuminate\Support\Str::limit($address, 26) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Batches Status Badge --}}
                            <div style="display: flex; align-items: center; gap: 5px;">
                                @if ($batchesCount > 0)
                                    <span class="pr-badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                                        📊 {{ $batchesCount }} Active
                                    </span>
                                @else
                                    <span class="pr-badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                        0 Batches
                                    </span>
                                @endif

                                @if ($archivedBatchesCount > 0)
                                    <span class="pr-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);" title="{{ $archivedBatchesCount }} Archived {{ \Illuminate\Support\Str::plural('Batch', $archivedBatchesCount) }}">
                                        📁 {{ $archivedBatchesCount }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- College Identity Header --}}
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 800; font-size: 15px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4); border: 1px solid rgba(255, 255, 255, 0.15);">
                                {{ $initials }}
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <h2 style="font-size: 16.5px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.35; letter-spacing: -0.01em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $college->name }}">
                                    {{ $college->name }}
                                </h2>
                                <div style="font-size: 12px; color: #8e909f; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $college->university_name ? 'Affiliated with ' . $college->university_name : 'Autonomous Institution' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Card Middle: Nested Stats Panel (#0b1326) ── --}}
                    <div style="background-color: #0b1326; border: 1px solid #1c2638; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                        {{-- 3 KPI Pill Stats --}}
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 9px; padding: 8px 6px; text-align: center;">
                                <div style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.04em;">Batches</div>
                                <div style="font-size: 18px; font-weight: 800; color: #ffffff; line-height: 1.2; margin-top: 2px;">{{ $batchesCount }}</div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 1px;">Cohorts</div>
                            </div>
                            <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 9px; padding: 8px 6px; text-align: center;">
                                <div style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.04em;">Active</div>
                                <div style="font-size: 18px; font-weight: 800; color: #34d399; line-height: 1.2; margin-top: 2px;">{{ $activeInternsCount }}</div>
                                <div style="font-size: 10px; color: #10b981; margin-top: 1px;">Current Interns</div>
                            </div>
                            <div style="background-color: #131b2e; border: 1px solid #1f293d; border-radius: 9px; padding: 8px 6px; text-align: center;">
                                <div style="font-size: 10px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.04em;">All Students</div>
                                <div style="font-size: 18px; font-weight: 800; color: #60a5fa; line-height: 1.2; margin-top: 2px;">{{ $internsCount }}</div>
                                <div style="font-size: 10px; color: #3b82f6; margin-top: 1px;">Total Records</div>
                            </div>
                        </div>

                        {{-- Address Row --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #94a3b8; padding-top: 6px; border-top: 1px solid #172238; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; flex: 1;">
                                <span>📍</span>
                                @if ($address)
                                    <span style="color: #cbd5e1; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $address }}">
                                        {{ $address }}
                                    </span>
                                @else
                                    <span style="color: #64748b; font-style: italic;">No address recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Card Bottom Action Buttons (Matching Progress Reports UI) ── --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 10px; border-top: 1px solid #1c2638;">
                        {{-- Left Actions: Edit & Delete Button --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a
                                href="{{ $editUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-secondary"
                                title="Edit College Details">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </a>

                            <button
                                type="button"
                                wire:click.stop="confirmDelete({{ $college->id }}, '{{ addslashes($college->name) }}')"
                                class="pr-icon-btn"
                                style="color: #ef4444;"
                                title="Delete College">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        {{-- Right Actions: + New Batch & View Batches --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a
                                href="{{ $createBatchUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-secondary"
                                style="background: rgba(59, 130, 246, 0.12); border-color: rgba(59, 130, 246, 0.35); color: #93c5fd;"
                                title="Create New Batch for this College">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>New Batch</span>
                            </a>

                            <a
                                href="{{ $viewUrl }}"
                                onclick="event.stopPropagation();"
                                class="pr-btn-primary"
                                title="View College & All Batches">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View Batches</span>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 10px;">
            {{ $colleges->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="pr-card" style="padding: 48px 24px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 16px; background-color: rgba(59, 130, 246, 0.1); color: #60a5fa; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; border: 1px solid rgba(59, 130, 246, 0.25);">
                <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0 0 6px 0;">
                No Colleges Found
            </h3>
            <p style="font-size: 13px; color: #8e909f; max-width: 440px; margin: 0 auto 20px auto;">
                @if (filled($search) || $universityFilter !== 'all')
                    No colleges match your current search or university filter. Try resetting the filters.
                @else
                    Start by registering your first college or academic institution to organize student cohorts and evaluations.
                @endif
            </p>
            <a href="{{ $createUrl }}"
               style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);">
                <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Register New College</span>
            </a>
        </div>
    @endif

    {{-- ── 5. Delete Confirmation Modal ── --}}
    @if (!empty($showDeleteModal))
        <div style="position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="pr-card" style="width: 100%; max-width: 440px; padding: 24px; border: 1.5px solid #222a3d; box-shadow: 0 20px 40px rgba(0,0,0,0.6);" wire:click.outside="closeDeleteModal">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background-color: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0;">Delete College?</h3>
                        <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">This action cannot be undone.</p>
                    </div>
                </div>

                <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin: 0 0 20px 0;">
                    Are you sure you want to remove <strong style="color: #ffffff;">"{{ $collegeNameToDelete }}"</strong>? All associated batch evaluations will also be affected.
                </p>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="pr-btn-secondary"
                        style="color: #94a3b8;">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="executeDelete"
                        style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #ffffff; border: none; font-size: 12.5px; font-weight: 700; padding: 8px 18px; border-radius: 9px; cursor: pointer; box-shadow: 0 2px 10px rgba(220, 38, 38, 0.4);">
                        Delete College
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
