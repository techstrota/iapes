<div class="space-y-4">
    @if(isset($batch))
        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-base flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $batch->interview_batch_name }}
                        <span class="text-xs px-2 py-0.5 rounded font-mono font-normal bg-gray-200/80 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                            {{ $batch->interview_batch_code }}
                        </span>
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                        <span>📅 {{ $batch->interview_date ? \Carbon\Carbon::parse($batch->interview_date)->format('d M, Y') : 'Date TBD' }}</span>
                        <span>•</span>
                        <span>⏰ {{ $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : '' }}</span>
                        @if($batch->interview_location)
                            <span>•</span>
                            <span>📍 {{ $batch->interview_location }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                    {{ $assignments->count() }} / {{ $batch->batch_size }} Filled
                </span>
                <a href="{{ \App\Filament\Resources\CandidateManagement\CandidateResource::getUrl('index', ['tableFilters' => ['interview_batch_id' => ['value' => $batch->id]], 'activeTab' => 'interview_scheduled']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                    Open Board
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($assignments as $assignment)
            @php
                $app = $assignment->application;
            @endphp
            <div class="p-3.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:border-primary-300 dark:hover:border-primary-700 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-gray-100 dark:border-gray-800/80">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-sm text-gray-900 dark:text-white">
                                {{ $app->name ?? 'Unknown Candidate' }}
                            </span>
                            @if($app && $app->application_code)
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 border border-primary-200/60 dark:border-primary-800/60">
                                    {{ $app->application_code }}
                                </span>
                            @endif
                            @if($app && $app->domain)
                                <span class="text-xs font-medium px-2 py-0.5 rounded bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/60">
                                    {{ $app->domain }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2 flex-wrap">
                            @if($app && $app->email)
                                <span>✉️ {{ $app->email }}</span>
                            @endif
                            @if($app && $app->phone)
                                <span>• 📞 {{ $app->phone }}</span>
                            @endif
                            @if($app && $app->college)
                                <span>• 🎓 {{ $app->college }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-center flex-wrap">
                        @if ($assignment->attendance === 'present')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                ✅ Present
                            </span>
                        @elseif ($assignment->attendance === 'absent')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                                ❌ Absent
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                ⏳ Pending Attendance
                            </span>
                        @endif

                        @if ($assignment->result === 'selected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                ⭐ Selected
                            </span>
                        @elseif ($assignment->result === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                                ✗ Rejected
                            </span>
                        @endif

                        @if($app)
                            <a href="{{ \App\Filament\Resources\CandidateManagement\CandidateResource::getUrl('view', ['record' => $app->id]) }}"
                               target="_blank"
                               title="Open candidate details"
                               class="p-1 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-white/10 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Evaluation marks grid --}}
                <div class="mt-2.5 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                            <span>Technical:</span>
                            <strong class="text-gray-900 dark:text-white">
                                {{ $assignment->problem_solving !== null ? $assignment->problem_solving . '/25' : '—' }}
                            </strong>
                        </div>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                            <span>Communication:</span>
                            <strong class="text-gray-900 dark:text-white">
                                {{ $assignment->communication !== null ? $assignment->communication . '/25' : '—' }}
                            </strong>
                        </div>
                    </div>

                    <div>
                        @if ($assignment->overall_score !== null)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-md
                                {{ $assignment->overall_score >= 40 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                                   ($assignment->overall_score >= 25 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' :
                                   'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300') }}">
                                Total: {{ $assignment->overall_score }} / 50
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">Not evaluated</span>
                        @endif
                    </div>
                </div>

                @if($assignment->remarks)
                    <div class="mt-2 pt-2 border-t border-dashed border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400 flex items-start gap-1">
                        <span class="font-medium text-gray-600 dark:text-gray-300 flex-shrink-0">Remarks:</span>
                        <span class="italic">{{ $assignment->remarks }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-10 px-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">No Candidates Assigned Yet</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                    Candidates can be assigned to this batch from the Candidates section when scheduling interviews.
                </p>
            </div>
        @endforelse
    </div>
</div>
