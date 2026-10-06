<x-filament-widgets::widget>
    @php
        $data = $this->getViewData();
        $tasksUrl = route('filament.intern.resources.task-management.assigned-tasks.index');
        $attendanceUrl = route('filament.intern.resources.attendances.index');
        $profileUrl = route('filament.intern.resources.interns.index');
        $idCardUrl = $data['internId'] ? route('print-id-card', ['id' => $data['internId']]) : null;
    @endphp

    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8"
         style="background: linear-gradient(135deg, #0b1326 0%, #131b2e 55%, #171f33 100%); border: 1.5px solid #222a3d; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);">

        {{-- Background ambient glow --}}
        <div class="absolute -right-16 -top-16 w-80 h-80 pointer-events-none rounded-full"
             style="background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(30, 64, 175, 0.05) 50%, transparent 75%);"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 pointer-events-none rounded-full"
             style="background: radial-gradient(circle, rgba(99, 102, 241, 0.10) 0%, transparent 70%);"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            {{-- Left column: Greeting & Details --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                          style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:#34d399;"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full" style="background:#10b981;"></span>
                        </span>
                        Active Session
                    </span>

                    @if($data['batch'])
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                              style="background-color: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.25);">
                            {{ $data['batch'] }}
                        </span>
                    @endif

                    @if($data['code'])
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-medium"
                              style="background-color: rgba(255, 255, 255, 0.06); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.1);">
                            {{ $data['code'] }}
                        </span>
                    @endif
                </div>

                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                        {{ $data['greeting'] }},
                        <span style="color: #60a5fa;">{{ explode(' ', $data['name'])[0] }}</span> 👋
                    </h1>
                    <p class="text-sm font-medium mt-1" style="color: #8e909f;">
                        {{ $data['role'] }} · Welcome back to your workspace!
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs font-medium" style="color: #8e909f;">
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-m-calendar-days class="w-4 h-4 text-blue-400" />
                        {{ $data['date'] }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-m-clock class="w-4 h-4 text-emerald-400" />
                        System Time: {{ now()->format('h:i A') }}
                    </span>
                </div>
            </div>

            {{-- Right column: Quick Navigation Shortcuts --}}
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <a href="{{ $tasksUrl }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-white transition-all hover:scale-105"
                   style="background-color: #1e40af; border: 1px solid #3b82f6; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);">
                    <x-heroicon-m-clipboard-document-check class="w-4 h-4 text-blue-200" />
                    <span>My Tasks</span>
                </a>

                <a href="{{ $attendanceUrl }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-200 transition-all hover:scale-105"
                   style="background-color: #171f33; border: 1px solid #222a3d;">
                    <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-400" />
                    <span>Attendance</span>
                </a>

                <a href="{{ $profileUrl }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-200 transition-all hover:scale-105"
                   style="background-color: #171f33; border: 1px solid #222a3d;">
                    <x-heroicon-m-user-circle class="w-4 h-4 text-indigo-400" />
                    <span>Profile</span>
                </a>

                @if($idCardUrl)
                    <a href="{{ $idCardUrl }}" target="_blank"
                       class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-200 transition-all hover:scale-105"
                       style="background-color: #171f33; border: 1px solid #222a3d;">
                        <x-heroicon-m-identification class="w-4 h-4 text-amber-400" />
                        <span>ID Card</span>
                    </a>
                @endif
            </div>

        </div>

    </div>
</x-filament-widgets::widget>