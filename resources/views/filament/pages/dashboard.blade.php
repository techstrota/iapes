<x-filament-panels::page class="fi-dashboard-page">
    @php
        $stats = $this->getStats();
    @endphp

    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

        {{-- 1. Hero Admin Welcome & Quick Actions Banner --}}
        <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
            {{-- Ambient Decorative Glow --}}
            <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1;">
                {{-- Left: Admin Identity & Status --}}
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 54px; height: 54px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 800; font-size: 22px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                                Welcome back, {{ auth()->user()?->name ?? 'Administrator' }}
                            </h1>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                Portal Admin
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 5px; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 6px #10b981;"></span>
                                Systems Operational
                            </span>
                            <span style="font-size: 12px; color: #8e909f; display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width: 14px; height: 14px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ now()->format('l, d F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Quick Action Launchpad --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    

                    <a href="{{ route('filament.admin.resources.attendances.index') }}"
                       style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                        <svg style="width: 16px; height: 16px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance
                    </a>

                    <a href="{{ route('filament.admin.resources.task-management.tasks.create') }}"
                       style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                        <svg style="width: 16px; height: 16px; color: #d8b4fe;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Task
                    </a>

                    <a href="{{ route('filament.admin.resources.candidate-management.candidates.index') }}"
                       style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                        <svg style="width: 16px; height: 16px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Candidates
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. Top 5 KPI Statistics Row --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 22px;">
            {{-- KPI 1: Total Interns --}}
            <a href="{{ route('filament.admin.resources.intern-management.interns.index') }}"
               style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Interns</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $stats['total_interns'] }}</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">{{ $stats['active_interns'] }} Active</span>
                    <span>{{ $stats['inactive_interns'] }} Completed</span>
                </div>
            </a>

            {{-- KPI 2: Today's Attendance --}}
            <a href="{{ route('filament.admin.resources.attendances.index') }}"
               style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Today's Attendance</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #4edea3; line-height: 1.1;">
                    {{ $stats['total_present_today'] }} <span style="font-size: 18px; color: #8e909f; font-weight: 600;">/ {{ $stats['active_interns'] }}</span>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #93c5fd; font-weight: 700;">{{ $stats['attendance_rate'] }}% Turnout</span>
                    <span>{{ $stats['wfh_today'] }} WFH • {{ $stats['absent_today'] }} Absent</span>
                </div>
            </a>

            {{-- KPI 3: Candidate Applications --}}
            <a href="{{ route('filament.admin.resources.candidate-management.candidates.index') }}"
               style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Recruitment Pool</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $stats['total_applications'] }}</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #4edea3; font-weight: 700;">{{ $stats['interview_selected'] }} Selected</span>
                    <span>{{ $stats['interview_assigned'] }} Scheduled</span>
                </div>
            </a>

            {{-- KPI 4: Active Batches & Squads --}}
            <a href="{{ route('filament.admin.resources.intern-management.internship-batches.index') }}"
               style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Active Batches</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $stats['active_batches'] }}</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #b8c4ff; font-weight: 700;">{{ $stats['active_teams'] }} Project Teams</span>
                    <span>{{ $stats['interview_batches'] }} Interview Hubs</span>
                </div>
            </a>

            {{-- KPI 5: Task Submissions --}}
            <a href="{{ route('filament.admin.resources.task-management.tasks.index') }}"
               style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Tasks & Review</span>
                    <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(147, 51, 234, 0.15); color: #d8b4fe; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(147, 51, 234, 0.3);">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
                <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $stats['total_tasks'] }}</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                    <span style="color: #fbbf24; font-weight: 700;">{{ $stats['pending_submissions'] }} Awaiting Review</span>
                    <span>{{ $stats['evaluated_submissions'] }} Evaluated</span>
                </div>
            </a>
        </div>

        {{-- 3. Operations Layout: 2 Columns (Main Workspaces vs Quick Management Hub) --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; align-items: start;">

            {{-- LEFT COLUMN: Daily Attendance Snapshot & Latest Applications Hub --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">

                {{-- A. Daily Attendance Status Strip --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; box-shadow: 0 0 6px #10b981;"></div>
                            <h2 style="font-size: 14px; font-weight: 700; color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 0.04em;">
                                Live Attendance Summary • {{ now()->format('d M Y') }}
                            </h2>
                        </div>
                        <a href="{{ route('filament.admin.resources.attendances.index') }}"
                           style="font-size: 12px; font-weight: 700; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            Open Attendance Hub
                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    {{-- 4 Mini Counters --}}
                    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px;">
                        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                            <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #4edea3;">Present</div>
                            <div style="font-size: 22px; font-weight: 800; color: #4edea3; margin-top: 2px;">{{ $stats['present_today'] }}</div>
                        </div>

                        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                            <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #93c5fd;">WFH</div>
                            <div style="font-size: 22px; font-weight: 800; color: #93c5fd; margin-top: 2px;">{{ $stats['wfh_today'] }}</div>
                        </div>

                        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                            <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fbbf24;">Leave</div>
                            <div style="font-size: 22px; font-weight: 800; color: #fbbf24; margin-top: 2px;">{{ $stats['leave_today'] }}</div>
                        </div>

                        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                            <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f87171;">Absent</div>
                            <div style="font-size: 22px; font-weight: 800; color: #f87171; margin-top: 2px;">{{ $stats['absent_today'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- B. Latest Applications & Candidate Queue --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                Latest Candidate Pipeline
                                <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    {{ $stats['applied_count'] }} Pending
                                </span>
                            </h2>
                            <p style="font-size: 12px; color: #8e909f; margin: 3px 0 0 0;">
                                Recent candidates waiting for evaluation and interview scheduling.
                            </p>
                        </div>
                        <a href="{{ route('filament.admin.resources.candidate-management.candidates.index') }}"
                           style="font-size: 12px; font-weight: 700; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            View All Candidates
                            <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    {{-- Embedded Filament Table Widget for Latest Applications --}}
                    <div style="margin-top: 10px;">
                        <x-filament-widgets::widgets
                            :columns="$this->getColumns()"
                            :widgets="$this->getVisibleWidgets()"
                        />
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Quick Management Hub & System Overview --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">

                {{-- Module Hub 1: Intern Lifecycle --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg style="width: 14px; height: 14px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Intern Management Hub
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="{{ route('filament.admin.resources.intern-management.interns.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                👥 Intern Directory
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['active_interns'] }} Active
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.intern-management.internship-batches.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                📦 Cohort Batches
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['active_batches'] }} Batches
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.intern-management.intern-teams.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                🚀 Project Teams & Squads
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #d8b4fe; background-color: rgba(147, 51, 234, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['active_teams'] }} Teams
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.intern-management.completion-documents.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                🎓 Completion Certificates & Letters
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.12); padding: 2px 7px; border-radius: 6px;">
                                Certificates
                            </span>
                        </a>
                    </div>
                </div>

                {{-- Module Hub 2: Recruitment & Task Operations --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Operations & Evaluation
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="{{ route('filament.admin.resources.candidate-management.interview-batches.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                🗓️ Interview Schedules
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['interview_assigned'] }} Scheduled
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.candidate-management.offer-letters.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                ✉️ Offer Letters Hub
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['offer_letters_count'] }} Issued
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.task-management.task-submission-and-evaluations.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                📋 Submissions & Evaluation
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.12); padding: 2px 7px; border-radius: 6px;">
                                {{ $stats['pending_submissions'] }} Awaiting
                            </span>
                        </a>

                        <a href="{{ route('filament.admin.resources.reports.index') }}"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                📊 Reports & Export Center
                            </span>
                            <span style="font-size: 11px; font-weight: 700; color: #dae2fd; background-color: rgba(255, 255, 255, 0.08); padding: 2px 7px; border-radius: 6px;">
                                Analytics
                            </span>
                        </a>
                    </div>
                </div>

                {{-- Module Hub 3: System Environment Summary --}}
                <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding-bottom: 8px; border-bottom: 1px solid #1c2638;">
                        <span>System Version</span>
                        <span style="font-weight: 700; color: #ffffff;">IAPES Enterprise v2.4</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding: 8px 0; border-bottom: 1px solid #1c2638;">
                        <span>Active Cohort</span>
                        <span style="font-weight: 700; color: #3b82f6;">{{ date('Y') }} Cycle</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding-top: 8px;">
                        <span>Audit Log Status</span>
                        <a href="{{ route('filament.admin.resources.activity-logs.index') }}"
                           style="color: #4edea3; font-weight: 700; text-decoration: none;">
                            Active Logs →
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-filament-panels::page>
