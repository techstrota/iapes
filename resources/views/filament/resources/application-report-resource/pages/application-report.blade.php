<x-filament-panels::page>
    @php
        $metrics = $this->metrics;
        $applications = $this->applications;
        $colDefs = $this->columnDefinitions;
        $totalCount = $metrics['totalApplications'];
        $activeCount = $metrics['activeApplications'];
        $archivedCount = $metrics['archivedApplications'];
    @endphp

    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', Helvetica, Arial, sans-serif; color: #e2e8f0; max-width: 100%; margin: 0 auto;">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- Premium Executive Design Tokens & Micro-Interactions               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <style>
            html {
                scroll-behavior: smooth;
            }
            .exec-card {
                background: linear-gradient(145deg, rgba(17, 26, 48, 0.9) 0%, rgba(10, 16, 31, 0.96) 100%);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 18px;
                box-shadow: 0 10px 32px -6px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.08);
                transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease;
            }
            .exec-card:hover {
                border-color: rgba(255, 255, 255, 0.15);
            }
            .exec-card-inner {
                background: rgba(14, 22, 41, 0.7);
                border: 1px solid rgba(255, 255, 255, 0.06);
                border-radius: 13px;
            }
            .exec-input {
                background: rgba(12, 19, 36, 0.85) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-radius: 10px !important;
                padding: 9px 13px !important;
                font-size: 13px !important;
                color: #f1f5f9 !important;
                outline: none !important;
                transition: all 0.2s ease !important;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.25) !important;
            }
            .exec-input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), inset 0 1px 2px rgba(0,0,0,0.1) !important;
                background: rgba(12, 19, 36, 0.98) !important;
            }

            /* Jump Nav Bar */
            .jump-nav-bar {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 10px;
                background: rgba(10, 16, 31, 0.85);
                backdrop-filter: blur(14px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 14px;
                margin-bottom: 22px;
                overflow-x: auto;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            }
            .jump-nav-link {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 7px 14px;
                border-radius: 9px;
                font-size: 12.5px;
                font-weight: 600;
                color: #94a3b8;
                text-decoration: none;
                transition: all 0.15s ease;
                white-space: nowrap;
            }
            .jump-nav-link:hover {
                background: rgba(59, 130, 246, 0.15);
                color: #93c5fd;
                transform: translateY(-1px);
            }

            /* Buttons */
            .exec-btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 9px;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: #ffffff;
                padding: 10px 22px;
                border-radius: 12px;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.01em;
                border: 1px solid rgba(255, 255, 255, 0.25);
                box-shadow: 0 4px 20px rgba(37, 99, 235, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.3);
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                text-decoration: none;
            }
            .exec-btn-primary:hover {
                background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
                box-shadow: 0 6px 26px rgba(37, 99, 235, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.35);
                transform: translateY(-2px);
            }
            .exec-btn-primary:active {
                transform: translateY(0);
                box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
            }

            .exec-btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background: rgba(28, 40, 65, 0.65);
                color: #cbd5e1;
                padding: 8px 14px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 600;
                border: 1px solid rgba(255, 255, 255, 0.08);
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none;
            }
            .exec-btn-secondary:hover {
                background: rgba(45, 62, 98, 0.85);
                color: #ffffff;
                border-color: rgba(255, 255, 255, 0.2);
                transform: translateY(-1px);
            }

            /* Archival segmented tabs */
            .exec-seg-btn {
                padding: 7px 16px;
                border-radius: 9px;
                font-size: 12.5px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                display: inline-flex;
                align-items: center;
                gap: 7px;
            }
            .exec-seg-active-all {
                background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;
            }
            .exec-seg-active-active {
                background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4) !important;
            }
            .exec-seg-active-archived {
                background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4) !important;
            }
            .exec-seg-inactive {
                background: transparent !important;
                color: #94a3b8 !important;
            }
            .exec-seg-inactive:hover {
                background: rgba(255, 255, 255, 0.05) !important;
                color: #f1f5f9 !important;
            }

            /* Column pill toggles */
            .col-toggle-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 11px;
                border-radius: 8px;
                font-size: 11.5px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.15s ease;
                user-select: none;
            }
            .col-toggle-on {
                background: rgba(59, 130, 246, 0.16);
                color: #93c5fd;
                border: 1px solid #3b82f6;
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
            }
            .col-toggle-on:hover {
                background: rgba(59, 130, 246, 0.26);
                transform: translateY(-1px);
            }
            .col-toggle-off {
                background: rgba(12, 19, 36, 0.7);
                color: #64748b;
                border: 1px dashed rgba(255, 255, 255, 0.1);
            }
            .col-toggle-off:hover {
                border-color: rgba(255, 255, 255, 0.25);
                color: #cbd5e1;
            }

            /* Table Row Hover & Sticky Header */
            .exec-th {
                position: sticky;
                top: 0;
                z-index: 10;
                background: rgba(12, 19, 36, 0.96) !important;
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                color: #94a3b8;
                font-size: 11.5px;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                white-space: nowrap;
            }
            .exec-tr {
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                transition: background-color 0.15s ease;
            }
            .exec-tr:hover {
                background-color: rgba(30, 41, 59, 0.5) !important;
            }

            /* Scrollbar */
            .exec-scroll::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            .exec-scroll::-webkit-scrollbar-track {
                background: #080d1a;
            }
            .exec-scroll::-webkit-scrollbar-thumb {
                background: #1e293b;
                border-radius: 3px;
            }
            .exec-scroll::-webkit-scrollbar-thumb:hover {
                background: #3b82f6;
            }
        </style>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 1. Executive Hero Header (Clean, Print & Reset Removed)           --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 24px 28px; margin-bottom: 16px; position: relative; overflow: hidden; border-top: 2px solid #3b82f6;">
            {{-- Ambient Decorative Glow --}}
            <div style="position: absolute; right: -50px; top: -50px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; left: 30%; top: -60px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1;">
                {{-- Title & Brand Identity --}}
                <div style="display: flex; align-items: center; gap: 18px;">
                    <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.25);">
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 23px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2; letter-spacing: -0.02em;">
                                Application Intelligence & Reports
                            </h1>
                            <span style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; background: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 10px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.35);">
                                Executive Suite
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 14px; margin-top: 6px; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #34d399; background: rgba(16, 185, 129, 0.12); padding: 2px 10px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                                Live Sync Telemetry
                            </span>
                            <span style="font-size: 12px; color: #94a3b8; display: inline-flex; align-items: center; gap: 5px;">
                                <svg style="width: 14px; height: 14px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ now()->format('l, d F Y') }}
                            </span>
                            <span style="font-size: 12px; color: #64748b;">&bull;</span>
                            <span style="font-size: 12px; color: #cbd5e1;">
                                <strong>{{ number_format($totalCount) }}</strong> Total Candidate Submissions
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Status Overview Pills --}}
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 600; color: #34d399; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 6px 14px; border-radius: 10px;">
                        {{ number_format($activeCount) }} Active
                    </span>
                    <span style="font-size: 12px; font-weight: 600; color: #fbbf24; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 6px 14px; border-radius: 10px;">
                        {{ number_format($archivedCount) }} Archived
                    </span>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 2. Five Executive Metric KPI Cards                                --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div id="section-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 14px; margin-bottom: 22px;">
            
            {{-- KPI 1: Total Applications --}}
            <div class="exec-card" style="padding: 18px; border-top: 2px solid #3b82f6;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8;">
                        Total Submissions
                    </span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <svg style="width: 19px; height: 19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1; letter-spacing: -0.02em;">
                    {{ number_format($totalCount) }}
                </div>
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #94a3b8;">Candidate Base</span>
                    <span style="font-size: 10.5px; font-weight: 700; color: #93c5fd; background: rgba(59, 130, 246, 0.15); padding: 2px 8px; border-radius: 6px;">
                        100% Overall
                    </span>
                </div>
            </div>

            {{-- KPI 2: Active Pipeline --}}
            <div class="exec-card" style="padding: 18px; border-top: 2px solid #10b981;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #34d399;">
                        Active Applications
                    </span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <svg style="width: 19px; height: 19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #34d399; line-height: 1; letter-spacing: -0.02em;">
                    {{ number_format($activeCount) }}
                </div>
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #94a3b8;">In Current Pipeline</span>
                    <span style="font-size: 10.5px; font-weight: 700; color: #6ee7b7; background: rgba(16, 185, 129, 0.15); padding: 2px 8px; border-radius: 6px;">
                        {{ $metrics['activePct'] }}% Active
                    </span>
                </div>
            </div>

            {{-- KPI 3: Archived Records --}}
            <div class="exec-card" style="padding: 18px; border-top: 2px solid #f59e0b;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #fbbf24;">
                        Archived Applications
                    </span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <svg style="width: 19px; height: 19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #fbbf24; line-height: 1; letter-spacing: -0.02em;">
                    {{ number_format($archivedCount) }}
                </div>
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #94a3b8;">Completed Cycles</span>
                    <span style="font-size: 10.5px; font-weight: 700; color: #fde68a; background: rgba(245, 158, 11, 0.15); padding: 2px 8px; border-radius: 6px;">
                        {{ $metrics['archivedPct'] }}% Archived
                    </span>
                </div>
            </div>

            {{-- KPI 4: Recruitment Cycles --}}
            <div class="exec-card" style="padding: 18px; border-top: 2px solid #8b5cf6;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #c084fc;">
                        Cohort Cycles
                    </span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3);">
                        <svg style="width: 19px; height: 19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1; letter-spacing: -0.02em;">
                    {{ number_format($metrics['cohortsCount']) }}
                </div>
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #94a3b8;">Named Drives</span>
                    <span style="font-size: 10.5px; font-weight: 700; color: #ddd6fe; background: rgba(139, 92, 246, 0.15); padding: 2px 8px; border-radius: 6px;">
                        Batches
                    </span>
                </div>
            </div>

            {{-- KPI 5: Shortlisted Conversion --}}
            <div class="exec-card" style="padding: 18px; border-top: 2px solid #06b6d4;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #22d3ee;">
                        Shortlisted
                    </span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(6, 182, 212, 0.15); display: flex; align-items: center; justify-content: center; color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3);">
                        <svg style="width: 19px; height: 19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1; letter-spacing: -0.02em;">
                    {{ number_format($metrics['statusCounts']['shortlisted'] ?? 0) }}
                </div>
                <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11.5px; color: #94a3b8;">Conversion</span>
                    <span style="font-size: 10.5px; font-weight: 700; color: #a5f3fc; background: rgba(6, 182, 212, 0.15); padding: 2px 8px; border-radius: 6px;">
                        {{ $metrics['conversionRate'] }}% Converted
                    </span>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 3. Visual Charts & Funnel Analytics                               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div id="section-charts" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px; margin-bottom: 22px;">
            
            {{-- Chart 1: Donut Pie Chart - Active vs Archived Proportion --}}
            <div class="exec-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <h2 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            Active vs. Archived Proportion
                        </h2>
                        <p style="font-size: 12px; color: #94a3b8; margin: 3px 0 0 0;">
                            Live candidate distribution across active hiring and archived cycles
                        </p>
                    </div>
                    <span style="font-size: 11px; font-weight: 600; color: #93c5fd; background: rgba(59, 130, 246, 0.12); padding: 3px 9px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.25);">
                        Ratio Chart
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-around; flex-wrap: wrap; gap: 24px; padding: 10px 0;">
                    {{-- SVG Donut Chart with Gradient Rings --}}
                    <div style="position: relative; width: 155px; height: 155px; flex-shrink: 0;">
                        <svg viewBox="0 0 140 140" style="transform: rotate(-90deg); width: 100%; height: 100%;">
                            {{-- Base Track --}}
                            <circle cx="70" cy="70" r="58" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="14"></circle>
                            
                            {{-- Active Segment (Emerald) --}}
                            @if($metrics['chart']['activeDash'] > 0)
                                <circle cx="70" cy="70" r="58" fill="none" stroke="#10b981" stroke-width="14"
                                    stroke-dasharray="{{ $metrics['chart']['activeDash'] }} {{ $metrics['chart']['circumference'] }}"
                                    stroke-linecap="round"
                                    style="transition: stroke-dasharray 0.6s ease; filter: drop-shadow(0 0 4px rgba(16, 185, 129, 0.4));"></circle>
                            @endif

                            {{-- Archived Segment (Amber) --}}
                            @if($metrics['chart']['archivedDash'] > 0)
                                <circle cx="70" cy="70" r="58" fill="none" stroke="#f59e0b" stroke-width="14"
                                    stroke-dasharray="{{ $metrics['chart']['archivedDash'] }} {{ $metrics['chart']['circumference'] }}"
                                    stroke-linecap="round"
                                    stroke-dashoffset="{{ $metrics['chart']['archivedOffset'] }}"
                                    style="transition: stroke-dasharray 0.6s ease, stroke-dashoffset 0.6s ease; filter: drop-shadow(0 0 4px rgba(245, 158, 11, 0.4));"></circle>
                            @endif
                        </svg>

                        {{-- Donut Center Metric --}}
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                            <span style="font-size: 22px; font-weight: 800; color: #ffffff; line-height: 1; letter-spacing: -0.02em;">
                                {{ number_format($totalCount) }}
                            </span>
                            <span style="font-size: 9.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px;">
                                Total Pool
                            </span>
                        </div>
                    </div>

                    {{-- Donut Breakdown Cards --}}
                    <div style="display: flex; flex-direction: column; gap: 10px; flex: 1; min-width: 170px;">
                        {{-- Active Pill Card --}}
                        <div class="exec-card-inner" style="padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; border-left: 3px solid #10b981;">
                            <div style="display: flex; align-items: center; gap: 9px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                                <span style="font-size: 13px; font-weight: 600; color: #f1f5f9;">Active Pipeline</span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 14.5px; font-weight: 800; color: #34d399;">
                                    {{ number_format($activeCount) }}
                                </div>
                                <div style="font-size: 11px; color: #94a3b8;">
                                    {{ $metrics['activePct'] }}%
                                </div>
                            </div>
                        </div>

                        {{-- Archived Pill Card --}}
                        <div class="exec-card-inner" style="padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; border-left: 3px solid #f59e0b;">
                            <div style="display: flex; align-items: center; gap: 9px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #f59e0b; display: inline-block; box-shadow: 0 0 8px #f59e0b;"></span>
                                <span style="font-size: 13px; font-weight: 600; color: #f1f5f9;">Archived Cycles</span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 14.5px; font-weight: 800; color: #fbbf24;">
                                    {{ number_format($archivedCount) }}
                                </div>
                                <div style="font-size: 11px; color: #94a3b8;">
                                    {{ $metrics['archivedPct'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Pipeline Funnel / Stage Distribution --}}
            <div class="exec-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <h2 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            Recruitment Pipeline Distribution
                        </h2>
                        <p style="font-size: 12px; color: #94a3b8; margin: 3px 0 0 0;">
                            Candidate volume across evaluation and qualification stages
                        </p>
                    </div>
                    <span style="font-size: 11px; font-weight: 600; color: #94a3b8; background: rgba(255, 255, 255, 0.05); padding: 3px 9px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.1);">
                        Funnel
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 11px;">
                    @php
                        $stages = [
                            ['key' => 'applied',             'label' => 'Applied',             'color' => '#3b82f6', 'grad' => 'linear-gradient(90deg, #2563eb, #60a5fa)'],
                            ['key' => 'interview_scheduled', 'label' => 'Interview Scheduled', 'color' => '#818cf8', 'grad' => 'linear-gradient(90deg, #6366f1, #a5b4fc)'],
                            ['key' => 'interviewed',         'label' => 'Interviewed',         'color' => '#06b6d4', 'grad' => 'linear-gradient(90deg, #0891b2, #67e8f9)'],
                            ['key' => 'shortlisted',         'label' => 'Shortlisted',         'color' => '#10b981', 'grad' => 'linear-gradient(90deg, #059669, #34d399)'],
                            ['key' => 'rejected',            'label' => 'Rejected',            'color' => '#ef4444', 'grad' => 'linear-gradient(90deg, #dc2626, #f87171)'],
                        ];
                        $tot = max(1, $totalCount);
                    @endphp

                    @foreach($stages as $stage)
                        @php
                            $cnt = $metrics['statusCounts'][$stage['key']] ?? 0;
                            $pct = round(($cnt / $tot) * 100, 1);
                        @endphp
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
                                <span style="font-weight: 600; color: #f1f5f9;">{{ $stage['label'] }}</span>
                                <span style="color: #94a3b8;">
                                    <strong style="color: #ffffff;">{{ number_format($cnt) }}</strong> 
                                    <span style="font-size: 11px;">({{ $pct }}%)</span>
                                </span>
                            </div>
                            <div style="width: 100%; height: 8px; background: rgba(12, 19, 36, 0.7); border-radius: 9999px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.06);">
                                <div style="width: {{ $pct }}%; height: 100%; background: {{ $stage['grad'] }}; border-radius: 9999px; transition: width 0.4s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 4. Archival Scope Segmented Control & Recruitment Cohort Toolbar --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 16px 20px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                {{-- Left: Archival Scope Segmented Tabs --}}
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                        Dataset Scope:
                    </span>
                    <div style="display: inline-flex; align-items: center; background: rgba(12, 19, 36, 0.85); padding: 3px; border-radius: 11px; border: 1px solid rgba(255, 255, 255, 0.08);">
                        {{-- All (Active + Archived) --}}
                        <button type="button" wire:click="setArchiveStatus('all')" class="exec-seg-btn {{ $archiveStatus === 'all' ? 'exec-seg-active-all' : 'exec-seg-inactive' }}">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>All Records</span>
                            <span style="font-size: 10px; font-weight: 800; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 9999px;">
                                {{ $totalCount }}
                            </span>
                        </button>

                        {{-- Active Only --}}
                        <button type="button" wire:click="setArchiveStatus('active')" class="exec-seg-btn {{ $archiveStatus === 'active' ? 'exec-seg-active-active' : 'exec-seg-inactive' }}">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            <span>Active Only</span>
                            <span style="font-size: 10px; font-weight: 800; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 9999px;">
                                {{ $activeCount }}
                            </span>
                        </button>

                        {{-- Archived Only --}}
                        <button type="button" wire:click="setArchiveStatus('archived')" class="exec-seg-btn {{ $archiveStatus === 'archived' ? 'exec-seg-active-archived' : 'exec-seg-inactive' }}">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <span>Archived Only</span>
                            <span style="font-size: 10px; font-weight: 800; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 9999px;">
                                {{ $archivedCount }}
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Right: Cohort Cycle Filter --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <label style="font-size: 12px; font-weight: 600; color: #94a3b8; white-space: nowrap;">
                        Recruitment Cohort Cycle:
                    </label>
                    <select wire:model.live="selectedCohort" class="exec-input" style="min-width: 230px;">
                        <option value="">All Cohort Cycles</option>
                        @foreach($metrics['cohortsList'] as $cName)
                            <option value="{{ $cName }}">{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 5. COLUMN ARCHITECT & DIRECT CSV DOWNLOAD (ID removed, grouped)   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div id="section-columns" class="exec-card" style="padding: 24px 26px; margin-bottom: 22px; border: 1.5px solid rgba(59, 130, 246, 0.35); background: linear-gradient(145deg, rgba(30, 58, 138, 0.25) 0%, rgba(12, 19, 36, 0.95) 100%);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                {{-- Left: Section Title & Info --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                            </svg>
                        </div>
                        <h2 style="font-size: 16.5px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                            Column Selector & CSV Download Hub
                        </h2>
                    </div>
                    <p style="font-size: 12.5px; color: #94a3b8; margin: 5px 0 0 44px;">
                        Customize your report output. All active columns below will be displayed in the table and exported in the CSV.
                    </p>
                </div>

                {{-- Right: Direct Download CSV Button & Helpers --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="button" wire:click="exportCsv" class="exec-btn-primary" title="Download CSV Report with currently selected columns & active filters">
                        <svg style="width: 17px; height: 17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download CSV Report</span>
                        <span style="background: rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 9999px;">
                            {{ count($this->visibleColumns) }} Cols
                        </span>
                    </button>

                    <button type="button" wire:click="selectAllColumns" class="exec-btn-secondary" style="padding: 9px 13px; font-size: 12px;">
                        Select All ({{ count($colDefs) }})
                    </button>

                    <button type="button" wire:click="resetColumns" class="exec-btn-secondary" style="padding: 9px 13px; font-size: 12px;">
                        Reset Default
                    </button>
                </div>
            </div>

            {{-- Categorized Column Toggles --}}
            <div style="display: flex; flex-direction: column; gap: 14px;">
                
                {{-- Group 1: Candidate & Academics --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 7px;">
                        <span style="font-size: 11px; font-weight: 700; color: #60a5fa; text-transform: uppercase; letter-spacing: 0.06em;">
                            🎓 Candidate & Academic Columns
                        </span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                        @foreach(['application_code', 'name', 'email', 'phone', 'college', 'degree', 'year', 'cgpa'] as $colKey)
                            @if(isset($colDefs[$colKey]))
                                @php $isVisible = $this->isColumnVisible($colKey); @endphp
                                <div wire:click="toggleColumn('{{ $colKey }}')" class="col-toggle-pill {{ $isVisible ? 'col-toggle-on' : 'col-toggle-off' }}" title="{{ $colDefs[$colKey]['description'] }}">
                                    <span style="font-size: 11px; font-weight: 800;">{!! $isVisible ? '&#10003;' : '&ndash;' !!}</span>
                                    <span>{{ $colDefs[$colKey]['label'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Group 2: Track & Technical Skills --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 7px;">
                        <span style="font-size: 11px; font-weight: 700; color: #34d399; text-transform: uppercase; letter-spacing: 0.06em;">
                            💼 Track, Skills & Status
                        </span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                        @foreach(['domain', 'duration', 'duration_unit', 'skills', 'resume_path', 'status'] as $colKey)
                            @if(isset($colDefs[$colKey]))
                                @php $isVisible = $this->isColumnVisible($colKey); @endphp
                                <div wire:click="toggleColumn('{{ $colKey }}')" class="col-toggle-pill {{ $isVisible ? 'col-toggle-on' : 'col-toggle-off' }}" title="{{ $colDefs[$colKey]['description'] }}">
                                    <span style="font-size: 11px; font-weight: 800;">{!! $isVisible ? '&#10003;' : '&ndash;' !!}</span>
                                    <span>{{ $colDefs[$colKey]['label'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Group 3: Archival & Timestamps --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 7px;">
                        <span style="font-size: 11px; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.06em;">
                            📦 Archival, Audit & Timestamps
                        </span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                        @foreach(['is_archived', 'cohort_archive_name', 'archive_note', 'archived_at', 'verification_token', 'created_at', 'updated_at'] as $colKey)
                            @if(isset($colDefs[$colKey]))
                                @php $isVisible = $this->isColumnVisible($colKey); @endphp
                                <div wire:click="toggleColumn('{{ $colKey }}')" class="col-toggle-pill {{ $isVisible ? 'col-toggle-on' : 'col-toggle-off' }}" title="{{ $colDefs[$colKey]['description'] }}">
                                    <span style="font-size: 11px; font-weight: 800;">{!! $isVisible ? '&#10003;' : '&ndash;' !!}</span>
                                    <span>{{ $colDefs[$colKey]['label'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Real-time export info hint --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 14px; border-top: 1px dashed rgba(255, 255, 255, 0.08); font-size: 11.5px; color: #94a3b8; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                    <span>Excel/CSV export will automatically include a <strong>Sr. No.</strong> column (1, 2, 3...) followed by your {{ count($this->visibleColumns) }} selected columns.</span>
                </div>
                <span style="color: #60a5fa; font-weight: 600;">
                    Export Ready ({{ number_format($applications->total()) }} records)
                </span>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 6. Filter Controls Toolbar (Search, Status, Domain, CGPA, Dates) --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="exec-card" style="padding: 18px 20px; margin-bottom: 22px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; align-items: end;">
                {{-- Search --}}
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Search Applications
                    </label>
                    <div style="position: relative;">
                        <input type="text" wire:model.live.debounce.350ms="search" placeholder="Name, code, email, phone, college, degree, domain, skills..." class="exec-input" style="width: 100%; padding-left: 36px !important;">
                        <svg style="position: absolute; left: 11px; top: 11px; width: 16px; height: 16px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Application Status
                    </label>
                    <select wire:model.live="status" class="exec-input" style="width: 100%;">
                        <option value="all">All Statuses</option>
                        <option value="applied">Applied</option>
                        <option value="interview_scheduled">Interview Scheduled</option>
                        <option value="interviewed">Interviewed</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                {{-- Domain Filter --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Domain / Track
                    </label>
                    <select wire:model.live="domain" class="exec-input" style="width: 100%;">
                        <option value="">All Domains</option>
                        @foreach($metrics['domainsList'] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Min CGPA --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Min CGPA
                    </label>
                    <input type="number" step="0.1" min="0" max="10" wire:model.live.debounce.300ms="minCgpa" placeholder="e.g. 7.5" class="exec-input" style="width: 100%;">
                </div>

                {{-- Time Range --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Time Period
                    </label>
                    <select wire:model.live="timeRange" class="exec-input" style="width: 100%;">
                        <option value="all">All Time</option>
                        <option value="7_days">Last 7 Days</option>
                        <option value="30_days">Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Date Range</option>
                    </select>
                </div>

                {{-- Rows Per Page --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-bottom: 5px;">
                        Page Size
                    </label>
                    <select wire:model.live="perPage" class="exec-input" style="width: 100%;">
                        <option value="15">15 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                    </select>
                </div>
            </div>

            {{-- Custom Date Range Picker (Visible only when timeRange === 'custom') --}}
            @if($timeRange === 'custom')
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed rgba(255, 255, 255, 0.08); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 12px; color: #94a3b8;">From:</label>
                        <input type="date" wire:model.live="customStartDate" class="exec-input">
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 12px; color: #94a3b8;">To:</label>
                        <input type="date" wire:model.live="customEndDate" class="exec-input">
                    </div>
                </div>
            @endif
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- 7. Applications Data Table (With Default Sr. No. Column)           --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div id="section-table" class="exec-card" style="overflow: hidden;">
            <div style="padding: 16px 22px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: rgba(14, 22, 41, 0.8);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h3 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: -0.01em;">
                        Applications Dataset
                    </h3>
                    <span style="font-size: 12px; color: #94a3b8;">
                        Showing {{ $applications->firstItem() ?? 0 }} - {{ $applications->lastItem() ?? 0 }} of {{ number_format($applications->total()) }} results
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11.5px; color: #94a3b8;">Active Columns:</span>
                    <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background: rgba(59, 130, 246, 0.2); padding: 2px 9px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.3);">
                        {{ count($this->visibleColumns) }} Visible + Sr. No.
                    </span>
                </div>
            </div>

            <div class="exec-scroll" style="overflow-x: auto; width: 100%; max-height: 700px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                    <thead>
                        <tr>
                            {{-- DEFAULT SR NO. COLUMN (Always Present) --}}
                            <th class="exec-th" style="padding: 13px 14px; font-weight: 700; width: 65px; text-align: center;">
                                Sr. No.
                            </th>

                            {{-- 1. Application Code --}}
                            @if($this->isColumnVisible('application_code'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('application_code')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>App Code</span>
                                        @if($sortBy === 'application_code') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 2. Name --}}
                            @if($this->isColumnVisible('name'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('name')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Applicant Name</span>
                                        @if($sortBy === 'name') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 3. Email --}}
                            @if($this->isColumnVisible('email'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('email')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Email</span>
                                        @if($sortBy === 'email') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 4. Phone --}}
                            @if($this->isColumnVisible('phone'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('phone')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Phone</span>
                                        @if($sortBy === 'phone') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 5. College --}}
                            @if($this->isColumnVisible('college'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('college')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>College</span>
                                        @if($sortBy === 'college') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 6. Degree --}}
                            @if($this->isColumnVisible('degree'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('degree')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Degree</span>
                                        @if($sortBy === 'degree') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 7. Year --}}
                            @if($this->isColumnVisible('year'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('year')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Year / Sem</span>
                                        @if($sortBy === 'year') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 8. CGPA --}}
                            @if($this->isColumnVisible('cgpa'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('cgpa')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>CGPA</span>
                                        @if($sortBy === 'cgpa') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 9. Domain --}}
                            @if($this->isColumnVisible('domain'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('domain')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Domain</span>
                                        @if($sortBy === 'domain') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 10. Duration --}}
                            @if($this->isColumnVisible('duration'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('duration')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Duration</span>
                                        @if($sortBy === 'duration') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 11. Duration Unit --}}
                            @if($this->isColumnVisible('duration_unit'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700;">
                                    Duration Unit
                                </th>
                            @endif

                            {{-- 12. Skills --}}
                            @if($this->isColumnVisible('skills'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700;">
                                    Skills
                                </th>
                            @endif

                            {{-- 13. Resume --}}
                            @if($this->isColumnVisible('resume_path'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700;">
                                    Resume
                                </th>
                            @endif

                            {{-- 14. Status --}}
                            @if($this->isColumnVisible('status'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('status')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Status</span>
                                        @if($sortBy === 'status') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 15. Archival State --}}
                            @if($this->isColumnVisible('is_archived'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('is_archived')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Archival State</span>
                                        @if($sortBy === 'is_archived') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 16. Cohort Cycle --}}
                            @if($this->isColumnVisible('cohort_archive_name'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('cohort_archive_name')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Cohort Cycle</span>
                                        @if($sortBy === 'cohort_archive_name') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 17. Archive Note --}}
                            @if($this->isColumnVisible('archive_note'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700;">
                                    Archive Note
                                </th>
                            @endif

                            {{-- 18. Archived At --}}
                            @if($this->isColumnVisible('archived_at'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('archived_at')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Archived At</span>
                                        @if($sortBy === 'archived_at') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 19. Verification Token --}}
                            @if($this->isColumnVisible('verification_token'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700;">
                                    Verification Token
                                </th>
                            @endif

                            {{-- 20. Created At --}}
                            @if($this->isColumnVisible('created_at'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('created_at')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Created At</span>
                                        @if($sortBy === 'created_at') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif

                            {{-- 21. Updated At --}}
                            @if($this->isColumnVisible('updated_at'))
                                <th class="exec-th" style="padding: 13px 16px; font-weight: 700; cursor: pointer;" wire:click="sort('updated_at')">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <span>Updated At</span>
                                        @if($sortBy === 'updated_at') <span style="color: #60a5fa;">{!! $sortDir === 'asc' ? '&uarr;' : '&darr;' !!}</span> @endif
                                    </div>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            @php
                                $srNo = ($applications->currentPage() - 1) * $applications->perPage() + $loop->iteration;
                            @endphp
                            <tr class="exec-tr">
                                
                                {{-- DEFAULT SR NO. BADGE (Always Present) --}}
                                <td style="padding: 14px 14px; text-align: center;">
                                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11.5px; font-weight: 700; color: #94a3b8; background: rgba(255, 255, 255, 0.05); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.08);">
                                        {{ str_pad($srNo, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                {{-- 1. Application Code --}}
                                @if($this->isColumnVisible('application_code'))
                                    <td style="padding: 14px 16px;">
                                        <span style="font-family: ui-monospace, SFMono-Regular, monospace; font-size: 11.5px; font-weight: 700; color: #93c5fd; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 7px; border-radius: 6px; white-space: nowrap;">
                                            {{ $app->application_code ?: ('APP/' . $app->id) }}
                                        </span>
                                    </td>
                                @endif

                                {{-- 2. Name --}}
                                @if($this->isColumnVisible('name'))
                                    <td style="padding: 14px 16px;">
                                        <div style="display: flex; align-items: center; gap: 9px; white-space: nowrap;">
                                            <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11.5px; color: #ffffff; flex-shrink: 0;">
                                                {{ strtoupper(substr($app->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <span style="font-weight: 700; color: #ffffff; font-size: 13px;">
                                                {{ $app->name ?: 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                @endif

                                {{-- 3. Email --}}
                                @if($this->isColumnVisible('email'))
                                    <td style="padding: 14px 16px; color: #cbd5e1; white-space: nowrap;">
                                        {{ $app->email ?: '--' }}
                                    </td>
                                @endif

                                {{-- 4. Phone --}}
                                @if($this->isColumnVisible('phone'))
                                    <td style="padding: 14px 16px; color: #cbd5e1; white-space: nowrap;">
                                        {{ $app->phone ?: '--' }}
                                    </td>
                                @endif

                                {{-- 5. College --}}
                                @if($this->isColumnVisible('college'))
                                    <td style="padding: 14px 16px; color: #ffffff; font-weight: 500; white-space: nowrap;">
                                        {{ $app->college ?: '--' }}
                                    </td>
                                @endif

                                {{-- 6. Degree --}}
                                @if($this->isColumnVisible('degree'))
                                    <td style="padding: 14px 16px; color: #cbd5e1; white-space: nowrap;">
                                        {{ $app->degree ?: '--' }}
                                    </td>
                                @endif

                                {{-- 7. Year --}}
                                @if($this->isColumnVisible('year'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @if($app->year)
                                            <span style="background: rgba(255, 255, 255, 0.08); padding: 2px 7px; border-radius: 5px; font-size: 11.5px; color: #cbd5e1;">
                                                {{ $app->year }}
                                            </span>
                                        @else
                                            <span style="color: #64748b;">--</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- 8. CGPA --}}
                                @if($this->isColumnVisible('cgpa'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @if($app->cgpa !== null)
                                            @php
                                                $cgpaVal = (float)$app->cgpa;
                                                $cgpaColor = $cgpaVal >= 8.0 ? '#10b981' : ($cgpaVal >= 6.5 ? '#3b82f6' : '#f59e0b');
                                                $cgpaBg = $cgpaVal >= 8.0 ? 'rgba(16, 185, 129, 0.15)' : ($cgpaVal >= 6.5 ? 'rgba(59, 130, 246, 0.15)' : 'rgba(245, 158, 11, 0.15)');
                                            @endphp
                                            <span style="font-weight: 800; font-size: 12px; color: {{ $cgpaColor }}; background: {{ $cgpaBg }}; padding: 2px 8px; border-radius: 6px; border: 1px solid {{ $cgpaColor }}40;">
                                                {{ number_format($cgpaVal, 2) }}
                                            </span>
                                        @else
                                            <span style="color: #64748b;">--</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- 9. Domain --}}
                                @if($this->isColumnVisible('domain'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        <span style="display: inline-block; background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 600;">
                                            {{ $app->domain ?: 'General' }}
                                        </span>
                                    </td>
                                @endif

                                {{-- 10. Duration --}}
                                @if($this->isColumnVisible('duration'))
                                    <td style="padding: 14px 16px; color: #e2e8f0; font-size: 12px; white-space: nowrap;">
                                        {{ $app->duration ?: '--' }}
                                    </td>
                                @endif

                                {{-- 11. Duration Unit --}}
                                @if($this->isColumnVisible('duration_unit'))
                                    <td style="padding: 14px 16px; color: #94a3b8; font-size: 12px; text-transform: capitalize; white-space: nowrap;">
                                        {{ $app->duration_unit ?: '--' }}
                                    </td>
                                @endif

                                {{-- 12. Skills --}}
                                @if($this->isColumnVisible('skills'))
                                    <td style="padding: 14px 16px; max-width: 220px; color: #cbd5e1; font-size: 12px;">
                                        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $app->skills }}">
                                            {{ $app->skills ?: '--' }}
                                        </div>
                                    </td>
                                @endif

                                {{-- 13. Resume --}}
                                @if($this->isColumnVisible('resume_path'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @if($app->resume_path)
                                            <a href="{{ asset('storage/' . $app->resume_path) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; color: #60a5fa; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 8px; border-radius: 6px; text-decoration: none;">
                                                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>View PDF</span>
                                            </a>
                                        @else
                                            <span style="color: #64748b; font-size: 11.5px;">No Resume</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- 14. Status --}}
                                @if($this->isColumnVisible('status'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @php
                                            $st = $app->status ?? 'applied';
                                            $badgeConfig = match($st) {
                                                'applied' => ['label' => 'Applied', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.15)'],
                                                'interview_scheduled' => ['label' => 'Interview Scheduled', 'color' => '#818cf8', 'bg' => 'rgba(129, 140, 248, 0.15)'],
                                                'interviewed' => ['label' => 'Interviewed', 'color' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.15)'],
                                                'shortlisted' => ['label' => 'Shortlisted', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.15)'],
                                                'rejected' => ['label' => 'Rejected', 'color' => '#ef4444', 'bg' => 'rgba(239, 68, 68, 0.15)'],
                                                default => ['label' => ucfirst(str_replace('_', ' ', $st)), 'color' => '#94a3b8', 'bg' => 'rgba(255,255,255,0.05)'],
                                            };
                                        @endphp
                                        <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 700; color: {{ $badgeConfig['color'] }}; background: {{ $badgeConfig['bg'] }}; padding: 3px 9px; border-radius: 9999px; border: 1px solid {{ $badgeConfig['color'] }}40;">
                                            <span style="width: 5px; height: 5px; border-radius: 50%; background-color: {{ $badgeConfig['color'] }};"></span>
                                            {{ $badgeConfig['label'] }}
                                        </span>
                                    </td>
                                @endif

                                {{-- 15. Archival State --}}
                                @if($this->isColumnVisible('is_archived'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @if($app->is_archived)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #fbbf24; background: rgba(245, 158, 11, 0.15); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.35);">
                                                📦 Archived
                                            </span>
                                        @else
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #34d399; background: rgba(16, 185, 129, 0.12); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                                🟢 Active
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                {{-- 16. Cohort Cycle --}}
                                @if($this->isColumnVisible('cohort_archive_name'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        @if($app->cohort_archive_name)
                                            <span style="font-size: 11.5px; font-weight: 600; color: #fde68a;">
                                                {{ $app->cohort_archive_name }}
                                            </span>
                                        @else
                                            <span style="color: #64748b;">--</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- 17. Archive Note --}}
                                @if($this->isColumnVisible('archive_note'))
                                    <td style="padding: 14px 16px; max-width: 200px; color: #cbd5e1; font-size: 12px;">
                                        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $app->archive_note }}">
                                            {{ $app->archive_note ?: '--' }}
                                        </div>
                                    </td>
                                @endif

                                {{-- 18. Archived At --}}
                                @if($this->isColumnVisible('archived_at'))
                                    <td style="padding: 14px 16px; color: #94a3b8; font-size: 12px; white-space: nowrap;">
                                        {{ $app->archived_at ? $app->archived_at->format('d M Y, h:i A') : '--' }}
                                    </td>
                                @endif

                                {{-- 19. Verification Token --}}
                                @if($this->isColumnVisible('verification_token'))
                                    <td style="padding: 14px 16px; max-width: 140px; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 11px; color: #64748b;">
                                        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $app->verification_token }}">
                                            {{ $app->verification_token ?: '--' }}
                                        </div>
                                    </td>
                                @endif

                                {{-- 20. Created At --}}
                                @if($this->isColumnVisible('created_at'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        <div style="color: #ffffff; font-size: 12px; font-weight: 600;">
                                            {{ $app->created_at ? $app->created_at->format('d M Y') : '--' }}
                                        </div>
                                        <div style="color: #94a3b8; font-size: 11px;">
                                            {{ $app->created_at ? $app->created_at->format('h:i A') : '' }}
                                        </div>
                                    </td>
                                @endif

                                {{-- 21. Updated At --}}
                                @if($this->isColumnVisible('updated_at'))
                                    <td style="padding: 14px 16px; white-space: nowrap;">
                                        <div style="color: #cbd5e1; font-size: 12px;">
                                            {{ $app->updated_at ? $app->updated_at->format('d M Y') : '--' }}
                                        </div>
                                        <div style="color: #64748b; font-size: 11px;">
                                            {{ $app->updated_at ? $app->updated_at->format('h:i A') : '' }}
                                        </div>
                                    </td>
                                @endif

                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" style="padding: 50px 20px; text-align: center;">
                                    <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(12, 19, 36, 0.8); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.08);">
                                        <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h4 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 4px 0;">
                                        No Applications Found
                                    </h4>
                                    <p style="font-size: 12.5px; color: #94a3b8; margin: 0;">
                                        No candidate applications match your current filters or scope.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer & Pagination --}}
            <div style="padding: 14px 22px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: rgba(12, 19, 36, 0.8);">
                <div style="font-size: 12px; color: #94a3b8;">
                    Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ number_format($applications->total()) }} candidate applications
                </div>
                <div>
                    {{ $applications->links() }}
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
