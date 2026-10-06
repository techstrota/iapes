<x-filament-panels::page class="fi-dashboard-page">
    <style>
        .fi-dashboard-page .fi-header {
            display: none !important;
        }
    </style>
    @php
        $data = $data ?? (isset($this) ? $this->getDashboardData() : []);
        $intern = $data['intern'] ?? null;
    @endphp

    @if(!$intern)
        <div style="padding: 24px; text-align: center; color: #8e909f; background: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px;">
            Loading intern dashboard data...
        </div>
    @else
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

            {{-- 1. Hero Intern Welcome & Quick Actions Banner (Matching Admin Hero Banner) --}}
            <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
                {{-- Ambient Decorative Glow --}}
                <div style="position: absolute; right: -40px; top: -40px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>
                <div style="position: absolute; left: -40px; bottom: -40px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1;">
                    {{-- Left: Intern Identity & Status --}}
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 800; font-size: 24px; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                            @if($intern->intern_image)
                                <img src="{{ asset('storage/' . $intern->intern_image) }}" alt="{{ $intern->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;" />
                            @else
                                {{ strtoupper(substr($intern->name ?? 'I', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                                    Welcome back, {{ $intern->name ?? 'Intern' }}
                                </h1>
                                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.3);">
                                    {{ $intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Intern') }}
                                </span>
                                @if($intern->intern_code)
                                    <span style="font-size: 11px; font-weight: 600; font-family: monospace; background-color: rgba(255, 255, 255, 0.06); color: #cbd5e1; padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.1);">
                                        {{ $intern->intern_code }}
                                    </span>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px; flex-wrap: wrap;">
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 6px #10b981;"></span>
                                    Active Workspace Session
                                </span>
                                <span style="font-size: 12px; color: #8e909f; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg style="width: 14px; height: 14px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ now()->format('l, d F Y') }}
                                </span>
                                @if($data['batch'])
                                    <span style="font-size: 12px; color: #93c5fd; display: inline-flex; align-items: center; gap: 4px;">
                                        🎓 {{ $data['batch']->batch_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Right: Quick Action Launchpad (matching Admin style) --}}
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('filament.intern.resources.task-management.assigned-tasks.index') }}"
                           style="display: inline-flex; align-items: center; gap: 7px; background-color: #1e40af; color: #ffffff; padding: 9px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 700; text-decoration: none; border: 1px solid #3b82f6; box-shadow: 0 2px 10px rgba(30, 64, 175, 0.4); transition: all 0.15s ease;">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            My Tasks
                            @if($data['pending_count'] > 0)
                                <span style="background-color: #f59e0b; color: #000000; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 9999px;">
                                    {{ $data['pending_count'] }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('filament.intern.resources.attendances.index') }}"
                           style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                            <svg style="width: 16px; height: 16px; color: #4edea3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Attendance
                        </a>

                        <a href="{{ route('filament.intern.resources.interns.index') }}"
                           style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                            <svg style="width: 16px; height: 16px; color: #93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            My Profile
                        </a>

                        <a href="{{ route('print-id-card', ['id' => $intern->id]) }}" target="_blank"
                           style="display: inline-flex; align-items: center; gap: 7px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                            <svg style="width: 16px; height: 16px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                            </svg>
                            ID Card
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. Top 5 KPI Statistics Row (Matching Admin Top 5 KPIs) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 22px;">

                {{-- KPI 1: Assigned Tasks --}}
                <a href="{{ route('filament.intern.resources.task-management.assigned-tasks.index') }}"
                   style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Assigned Tasks</span>
                        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(99, 102, 241, 0.15); color: #b8c4ff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(99, 102, 241, 0.3);">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                            </svg>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">{{ $data['total_assigned'] }}</div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                        <span style="color: {{ $data['pending_count'] > 0 ? '#fbbf24' : '#4edea3' }}; font-weight: 700;">
                            {{ $data['pending_count'] }} Action Needed
                        </span>
                        <span>{{ $data['approved_count'] }} Approved</span>
                    </div>
                </a>

                {{-- KPI 2: Attendance Rate --}}
                <a href="{{ route('filament.intern.resources.attendances.index') }}"
                   style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Attendance Rate</span>
                        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(78, 222, 163, 0.3);">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #4edea3; line-height: 1.1;">
                        {{ $data['attendance_rate'] }}%
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                        <span style="color: #93c5fd; font-weight: 700;">{{ $data['present_days'] }} / {{ $data['total_attendance_days'] }} Present</span>
                        <span>{{ $data['late_days'] }} Late · {{ $data['leave_days'] }} Leave</span>
                    </div>
                </a>

                {{-- KPI 3: Average Evaluation Score --}}
                <a href="{{ route('filament.intern.resources.task-management.assigned-tasks.index') }}?activeTab=approved"
                   style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Average Evaluation</span>
                        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.3);">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div style="font-size: 30px; font-weight: 800; color: #ffffff; line-height: 1.1;">
                        {{ $data['avg_score'] ? $data['avg_score'] . ' / 100' : '—' }}
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: #8e909f;">
                        <span style="color: #4edea3; font-weight: 700;">{{ $data['review_count'] }} In Review</span>
                        <span>{{ $data['rejected_count'] }} Revision Needed</span>
                    </div>
                </a>

                

                {{-- KPI 5: Project Team & Squad --}}
                <a href="{{ route('filament.intern.resources.interns.index') }}"
                   style="text-decoration: none; display: block; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); transition: transform 0.15s ease, border-color 0.15s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Assigned Squad</span>
                        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.3);">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div style="font-size: 20px; font-weight: 800; color: #ffffff; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $data['team']?->team_name ?? 'Individual Work' }}
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px; font-size: 11.5px; color: #8e909f;">
                        <span style="color: #b8c4ff; font-weight: 700;">{{ $data['batch']?->batch_name ?? 'Active Batch' }}</span>
                        <span>{{ $data['teammates']->count() }} Teammates</span>
                    </div>
                </a>

            </div>

            {{-- 3. Operations Layout: 2 Columns (Matching Admin 2fr 1fr Grid) --}}
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; align-items: start;">

                {{-- LEFT COLUMN: Daily Tasks Action Center & Live Attendance Strip & Recent Feedback --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    {{-- A. Daily Activity & Priority Tasks Execution Hub --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 20px 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6; box-shadow: 0 0 6px #3b82f6;"></div>
                                <h2 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Daily Tasks & Priority Work Queue
                                </h2>
                                <span style="font-size: 11px; font-weight: 700; background-color: {{ $data['pending_count'] > 0 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.15)' }}; color: {{ $data['pending_count'] > 0 ? '#fbbf24' : '#4edea3' }}; padding: 2px 8px; border-radius: 9999px; border: 1px solid {{ $data['pending_count'] > 0 ? 'rgba(245, 158, 11, 0.3)' : 'rgba(16, 185, 129, 0.3)' }};">
                                    {{ $data['pending_count'] }} Action Needed
                                </span>
                            </div>
                            <a href="{{ route('filament.intern.resources.task-management.assigned-tasks.index') }}"
                               style="font-size: 12px; font-weight: 700; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                View All Tasks
                                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        {{-- Tasks List --}}
                        @if(count($data['pending_tasks']) === 0)
                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 12px; padding: 28px; text-align: center;">
                                <div style="width: 48px; height: 48px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.15); color: #4edea3; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
                                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h3 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 4px 0;">All Caught Up! 🎉</h3>
                                <p style="font-size: 12.5px; color: #8e909f; margin: 0;">
                                    You have submitted all currently assigned tasks. New tasks from your mentor or administrator will appear here.
                                </p>
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @foreach($data['pending_tasks'] as $item)
                                    @php
                                        $t = $item['task'];
                                        $isRejected = $item['status'] === 'rejected';
                                        $due = $t->due_date ? \Carbon\Carbon::parse($t->due_date)->startOfDay() : null;
                                        $diff = $due ? now()->startOfDay()->diffInDays($due, false) : null;
                                    @endphp
                                    <div style="background-color: #090e1c; border: 1px solid #222a3d; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; transition: border-color 0.15s ease;">
                                        <div style="flex: 1; min-width: 240px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                                                <span style="font-size: 14px; font-weight: 700; color: #ffffff;">
                                                    {{ $t->title }}
                                                </span>

                                                {{-- Priority Badge --}}
                                                @if($t->priority === 'high')
                                                    <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; background-color: rgba(239, 68, 68, 0.15); color: #f87171; padding: 2px 7px; border-radius: 9999px; border: 1px solid rgba(239, 68, 68, 0.3);">
                                                        High Priority
                                                    </span>
                                                @elseif($t->priority === 'medium')
                                                    <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 2px 7px; border-radius: 9999px; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                        Medium
                                                    </span>
                                                @endif

                                                {{-- Status Badge --}}
                                                @if($isRejected)
                                                    <span style="font-size: 10px; font-weight: 700; background-color: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 2px 7px; border-radius: 9999px;">
                                                        ⚠ Needs Revision
                                                    </span>
                                                @endif
                                            </div>

                                            <p style="font-size: 12px; color: #8e909f; margin: 0 0 6px 0; line-height: 1.4;">
                                                {{ \Illuminate\Support\Str::limit($t->description ?? 'No specific instructions provided.', 90) }}
                                            </p>

                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 11.5px;">
                                                @if($due)
                                                    <span style="color: {{ $diff < 0 ? '#f87171' : ($diff <= 2 ? '#fbbf24' : '#94a3b8') }}; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                        <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        @if($diff < 0)
                                                            Overdue by {{ abs((int)$diff) }}d ({{ $due->format('M d') }})
                                                        @elseif($diff === 0)
                                                            Due Today!
                                                        @elseif($diff <= 2)
                                                            Due in {{ (int)$diff }} day(s) ({{ $due->format('M d') }})
                                                        @else
                                                            Due: {{ $due->format('M d, Y') }}
                                                        @endif
                                                    </span>
                                                @endif

                                                @if($t->attachment)
                                                    <a href="{{ asset('storage/' . $t->attachment) }}" target="_blank"
                                                       style="color: #60a5fa; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                                                        📎 Brief File
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Action Button: Submit Work --}}
                                        <div>
                                            <button type="button"
                                                    wire:click="mountAction('submitWork', { task_id: {{ $t->id }}, task_title: '{{ addslashes($t->title) }}' })"
                                                    style="display: inline-flex; align-items: center; gap: 6px; background-color: #1e40af; color: #ffffff; padding: 8px 14px; border-radius: 9px; font-size: 12px; font-weight: 700; border: 1px solid #3b82f6; cursor: pointer; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4); transition: background-color 0.15s ease;">
                                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                {{ $isRejected ? 'Resubmit Work' : 'Submit Work' }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- B. Daily Attendance Snapshot Strip (Matching Admin Attendance Live Summary) --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; box-shadow: 0 0 6px #10b981;"></div>
                                <h2 style="font-size: 14px; font-weight: 700; color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Daily Attendance Summary • {{ now()->format('d M Y') }}
                                </h2>
                            </div>
                            <a href="{{ route('filament.intern.resources.attendances.index') }}"
                               style="font-size: 12px; font-weight: 700; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                Open Full Log
                                <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        {{-- Today's Status Banner --}}
                        <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($data['today_attendance'])
                                    @php
                                        $tStatus = strtolower($data['today_attendance']->status);
                                    @endphp
                                    @if($tStatus === 'present')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #4edea3;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                                            Marked Present for Today
                                        </span>
                                    @elseif($tStatus === 'late')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #fbbf24;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b;"></span>
                                            Marked Late Arrival for Today
                                        </span>
                                    @elseif($tStatus === 'leave')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #93c5fd;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span>
                                            On Approved Leave Today
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #f87171;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #ef4444;"></span>
                                            Marked Absent Today
                                        </span>
                                    @endif
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #8e909f;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #64748b;"></span>
                                        Today's attendance being processed by Admin
                                    </span>
                                @endif
                            </div>
                            <span style="font-size: 12px; color: #8e909f;">
                                Cumulative: <strong style="color: #4edea3;">{{ $data['attendance_rate'] }}%</strong>
                            </span>
                        </div>

                        {{-- 4 Mini Counters (Matching Admin) --}}
                        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px;">
                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #4edea3;">Present</div>
                                <div style="font-size: 22px; font-weight: 800; color: #4edea3; margin-top: 2px;">{{ $data['present_days'] }}</div>
                            </div>

                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #fbbf24;">Late</div>
                                <div style="font-size: 22px; font-weight: 800; color: #fbbf24; margin-top: 2px;">{{ $data['late_days'] }}</div>
                            </div>

                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #93c5fd;">Leave</div>
                                <div style="font-size: 22px; font-weight: 800; color: #93c5fd; margin-top: 2px;">{{ $data['leave_days'] }}</div>
                            </div>

                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f87171;">Absent</div>
                                <div style="font-size: 22px; font-weight: 800; color: #f87171; margin-top: 2px;">{{ $data['absent_days'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- C. Recent Submissions & Feedback Stream --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h2 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    Recent Submissions & Evaluation Feedback
                                </h2>
                                <p style="font-size: 12px; color: #8e909f; margin: 3px 0 0 0;">
                                    Track evaluation scores, reviewer grades, and mentor feedback on your deliverables.
                                </p>
                            </div>
                        </div>

                        @if($data['recent_submissions']->isEmpty())
                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 20px; text-align: center; color: #8e909f; font-size: 13px;">
                                No task submissions recorded yet. Once you submit a deliverable, evaluation notes will appear here.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @foreach($data['recent_submissions'] as $sub)
                                    <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                        <div style="flex: 1; min-width: 200px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 3px;">
                                                <span style="font-size: 13.5px; font-weight: 700; color: #ffffff;">
                                                    {{ $sub->task?->title ?? 'Task Deliverable' }}
                                                </span>

                                                {{-- Status --}}
                                                @if($sub->status === 'approved')
                                                    <span style="font-size: 10.5px; font-weight: 700; background-color: rgba(16, 185, 129, 0.15); color: #34d399; padding: 2px 7px; border-radius: 9999px;">
                                                        Approved
                                                    </span>
                                                @elseif($sub->status === 'rejected')
                                                    <span style="font-size: 10.5px; font-weight: 700; background-color: rgba(239, 68, 68, 0.15); color: #f87171; padding: 2px 7px; border-radius: 9999px;">
                                                        Needs Revision
                                                    </span>
                                                @else
                                                    <span style="font-size: 10.5px; font-weight: 700; background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 2px 7px; border-radius: 9999px;">
                                                        Under Review
                                                    </span>
                                                @endif
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 11.5px; color: #8e909f;">
                                                <span>Submitted: {{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('M d, Y') : '—' }}</span>
                                                @if($sub->marks !== null)
                                                    <span style="color: #4edea3; font-weight: 700;">Score: {{ $sub->marks }}/100</span>
                                                @endif
                                                @if($sub->grade)
                                                    <span style="color: #93c5fd; font-weight: 700;">Grade: {{ $sub->grade }}</span>
                                                @endif
                                            </div>

                                            @if($sub->admin_feedback)
                                                <div style="margin-top: 6px; font-size: 12px; color: #dae2fd; background-color: rgba(255, 255, 255, 0.03); border-left: 2px solid #3b82f6; padding: 4px 8px; border-radius: 0 4px 4px 0;">
                                                    💬 {{ \Illuminate\Support\Str::limit($sub->admin_feedback, 120) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            @if($sub->submission_file)
                                                <a href="{{ asset('storage/' . $sub->submission_file) }}" target="_blank"
                                                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px; background-color: #171f33; border: 1px solid #222a3d; border-radius: 7px; color: #93c5fd; font-size: 11.5px; text-decoration: none;">
                                                    ⬇ File
                                                </a>
                                            @endif

                                            @if($sub->submission_text)
                                                <a href="{{ $sub->submission_text }}" target="_blank"
                                                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px; background-color: #171f33; border: 1px solid #222a3d; border-radius: 7px; color: #60a5fa; font-size: 11.5px; text-decoration: none;">
                                                    🔗 Link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                {{-- RIGHT COLUMN: Quick Management Hub & Team Squad & System Milestones --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    {{-- Module Hub 1: Quick Action Launchpad --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 14px; height: 14px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Intern Launchpad
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="{{ route('filament.intern.resources.task-management.assigned-tasks.index') }}"
                               style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    📋 Assigned Task Board
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: {{ $data['pending_count'] > 0 ? '#fbbf24' : '#4edea3' }}; background-color: {{ $data['pending_count'] > 0 ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.12)' }}; padding: 2px 7px; border-radius: 6px;">
                                    {{ $data['pending_count'] }} Pending
                                </span>
                            </a>

                            <a href="{{ route('filament.intern.resources.attendances.index') }}"
                               style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    📅 Attendance History
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.12); padding: 2px 7px; border-radius: 6px;">
                                    {{ $data['attendance_rate'] }}%
                                </span>
                            </a>

                            <a href="{{ route('print-id-card', ['id' => $intern->id]) }}" target="_blank"
                               style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    🪪 Official ID Card
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.12); padding: 2px 7px; border-radius: 6px;">
                                    Print / PDF
                                </span>
                            </a>

                            @if($intern->offer_letter_id)
                                <a href="{{ route('view-offer-pdf', ['id' => $intern->offer_letter_id]) }}" target="_blank"
                                   style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        📄 Offer Letter Document
                                    </span>
                                    <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.12); padding: 2px 7px; border-radius: 6px;">
                                        View PDF
                                    </span>
                                </a>
                            @endif

                            @if(filled($intern->cert_token) || $intern->completionCertificate)
                                <a href="{{ route('intern.certificate.view', ['id' => $intern->id]) }}" target="_blank"
                                   style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        🎓 Completion Certificate
                                    </span>
                                    <span style="font-size: 11px; font-weight: 700; color: #34d399; background-color: rgba(16, 185, 129, 0.15); padding: 2px 7px; border-radius: 6px;">
                                        Issued
                                    </span>
                                </a>
                            @endif

                            @if($intern->completionLetter)
                                <a href="{{ route('intern.completion_letter.view', ['id' => $intern->id]) }}" target="_blank"
                                   style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; text-decoration: none; color: #dae2fd; font-size: 13px; font-weight: 600; transition: all 0.15s ease;">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        📜 Experience Letter
                                    </span>
                                    <span style="font-size: 11px; font-weight: 700; color: #38bdf8; background-color: rgba(56, 189, 248, 0.15); padding: 2px 7px; border-radius: 6px;">
                                        Available
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Module Hub 2: Cohort & Project Squad --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 18px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8e909f; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Cohort & Project Team
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Active Cohort</div>
                                <div style="font-size: 14px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                                    {{ $data['batch']?->batch_name ?? 'General Cohort' }}
                                </div>
                                <div style="font-size: 11.5px; color: #60a5fa; margin-top: 3px;">
                                    Timing: {{ $data['batch']?->batch_timing ?? 'Flexible Shift' }}
                                </div>
                            </div>

                            <div style="background-color: #090e1c; border: 1px solid #1c2638; border-radius: 10px; padding: 12px;">
                                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #8e909f;">Assigned Team</div>
                                <div style="font-size: 14px; font-weight: 700; color: #34d399; margin-top: 2px;">
                                    {{ $data['team']?->team_name ?? 'Individual Contributor' }}
                                </div>
                                @if($data['teammates']->isNotEmpty())
                                    <div style="margin-top: 8px;">
                                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #8e909f; margin-bottom: 4px;">Teammates:</div>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            @foreach($data['teammates'] as $mate)
                                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #cbd5e1;">
                                                    <span>👤 {{ $mate->name }}</span>
                                                    <span style="font-size: 10.5px; color: #8e909f;">{{ $mate->intern_code }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Module Hub 3: Milestone Timeline & Workspace Info --}}
                    <div style="background-color: #131b2e; border: 1px solid #222a3d; border-radius: 14px; padding: 16px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding-bottom: 8px; border-bottom: 1px solid #1c2638;">
                            <span>Start Date</span>
                            <span style="font-weight: 700; color: #ffffff;">{{ $data['start_date'] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding: 8px 0; border-bottom: 1px solid #1c2638;">
                            <span>Completion Date</span>
                            <span style="font-weight: 700; color: #3b82f6;">{{ $data['end_date'] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding: 8px 0; border-bottom: 1px solid #1c2638;">
                            <span>Total Duration</span>
                            <span style="font-weight: 700; color: #ffffff;">{{ $data['total_duration'] }} Days</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8e909f; padding-top: 8px;">
                            <span>Account Status</span>
                            <span style="color: #4edea3; font-weight: 700;">
                                ● Active Intern
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    @endif
</x-filament-panels::page>
