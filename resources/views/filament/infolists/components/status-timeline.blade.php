@php
    $record = $getRecord();
    $currentStatus = $record->status;
    
    // Define the sequence of steps
    $steps = [
        'applied' => [
            'label' => 'Applied',
            'desc' => 'Candidate application submitted',
            'icon' => 'heroicon-m-inbox',
            'active_bg' => 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white',
        ],
        'interview_scheduled' => [
            'label' => 'Interview Scheduled',
            'desc' => 'Assigned to interview batch',
            'icon' => 'heroicon-m-calendar',
            'active_bg' => 'bg-gradient-to-br from-sky-500 to-blue-600 text-white',
        ],
        'interviewed' => [
            'label' => 'Interviewed',
            'desc' => 'Technical & HR evaluation completed',
            'icon' => 'heroicon-m-check-badge',
            'active_bg' => 'bg-gradient-to-br from-amber-500 to-orange-600 text-white',
        ],
    ];

    // Determine final step based on whether shortlisted/rejected or pending
    if ($currentStatus === 'rejected') {
        $steps['rejected'] = [
            'label' => 'Rejected',
            'desc' => 'Candidate application closed',
            'icon' => 'heroicon-m-x-circle',
            'active_bg' => 'bg-gradient-to-br from-rose-600 to-red-700 text-white',
        ];
    } else {
        $steps['shortlisted'] = [
            'label' => 'Shortlisted',
            'desc' => 'Selected for internship offer',
            'icon' => 'heroicon-m-star',
            'active_bg' => 'bg-gradient-to-br from-emerald-500 to-green-600 text-white',
        ];
    }

    $statusOrder = ['applied', 'interview_scheduled', 'interviewed', 'shortlisted', 'rejected'];
    $currentIndex = array_search($currentStatus, $statusOrder);
    if ($currentIndex === false) $currentIndex = -1;
@endphp

<div class="relative pl-1 space-y-4 before:absolute before:top-4 before:bottom-4 before:left-[1.625rem] before:w-[2px] before:bg-[#1e2a42]">
    @foreach($steps as $key => $step)
        @php
            $stepIndex = array_search($key, $statusOrder);
            $isPastOrCurrent = $stepIndex <= $currentIndex;
            $isCurrent = $stepIndex === $currentIndex;
            
            $nodeBg = $isCurrent
                ? $step['active_bg'] . ' ring-4 ring-blue-500/25 shadow-lg shadow-blue-500/30'
                : ($isPastOrCurrent 
                    ? $step['active_bg'] . ' ring-4 ring-[#111a2e]' 
                    : 'bg-[#0e1626] border border-[#22304d] text-slate-500 ring-4 ring-[#111a2e]');
            
            $cardBorder = $isCurrent 
                ? 'bg-[#131d33] border border-[#2a3c5e] shadow-md shadow-black/20' 
                : ($isPastOrCurrent ? 'bg-[#0f172a]/60 border border-[#1b263b]' : 'bg-[#0b1222]/40 border border-[#162035]/60 opacity-70');
            
            $titleColor = $isCurrent ? 'text-white font-bold' : ($isPastOrCurrent ? 'text-slate-200 font-semibold' : 'text-slate-400 font-medium');
        @endphp
        
        <div class="relative flex items-center gap-3.5">
            <!-- Timeline Dot / Icon -->
            <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 {{ $nodeBg }}">
                <x-icon name="{{ $step['icon'] }}" class="w-4 h-4" />
            </div>
            
            <!-- Step Card Content -->
            <div class="flex-1 {{ $cardBorder }} rounded-xl px-3.5 py-2.5 transition-all">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <p class="{{ $titleColor }} text-sm leading-snug">
                        {{ $step['label'] }}
                    </p>
                    
                    @if($isCurrent)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-500/15 text-sky-400 border border-sky-500/30">
                            Current Stage
                        </span>
                    @elseif($isPastOrCurrent)
                        <span class="inline-flex items-center text-[11px] font-semibold text-emerald-400 gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Completed
                        </span>
                    @endif
                </div>
                
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $step['desc'] }}
                </p>
                
                @if($key === 'interviewed' && $isPastOrCurrent)
                    @php 
                        $latestAssignment = $record->interviewAssignments()->latest()->first(); 
                    @endphp
                    @if($latestAssignment && $latestAssignment->overall_score !== null)
                        <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                            <x-icon name="heroicon-m-trophy" class="w-3.5 h-3.5 text-amber-400" />
                            Evaluation Score: {{ $latestAssignment->overall_score }} / 50
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
