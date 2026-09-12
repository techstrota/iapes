@php
    $record = $getRecord();
    $currentStatus = $record->status;
    
    // Define the sequence of steps
    $steps = [
        'applied' => [
            'label' => 'Applied',
            'icon' => 'heroicon-m-inbox',
            'color' => 'text-gray-500',
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'active_bg' => 'bg-gray-500',
        ],
        'interview_scheduled' => [
            'label' => 'Interview Scheduled',
            'icon' => 'heroicon-m-calendar',
            'color' => 'text-blue-500',
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'active_bg' => 'bg-blue-500',
        ],
        'interviewed' => [
            'label' => 'Interviewed',
            'icon' => 'heroicon-m-check-badge',
            'color' => 'text-amber-500',
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'active_bg' => 'bg-amber-500',
        ],
    ];

    // Determine final step based on whether shortlisted/rejected or pending
    if ($currentStatus === 'rejected') {
        $steps['rejected'] = [
            'label' => 'Rejected',
            'icon' => 'heroicon-m-x-circle',
            'color' => 'text-red-500',
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'active_bg' => 'bg-red-500',
        ];
    } else {
        $steps['shortlisted'] = [
            'label' => 'Shortlisted',
            'icon' => 'heroicon-m-star',
            'color' => 'text-green-500',
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'active_bg' => 'bg-green-500',
        ];
    }

    $statusOrder = ['applied', 'interview_scheduled', 'interviewed', 'shortlisted', 'rejected'];
    $currentIndex = array_search($currentStatus, $statusOrder);
    if ($currentIndex === false) $currentIndex = -1; // For pending/verified if ever shown
@endphp

<div class="relative pl-4 space-y-8 before:absolute before:inset-y-0 before:left-6 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
    @foreach($steps as $key => $step)
        @php
            $stepIndex = array_search($key, $statusOrder);
            $isPastOrCurrent = $stepIndex <= $currentIndex;
            $isCurrent = $stepIndex === $currentIndex;
            
            // Dynamic styling based on state
            $iconBg = $isPastOrCurrent ? $step['active_bg'] : $step['bg'];
            $iconColor = $isPastOrCurrent ? 'text-white' : 'text-gray-400 dark:text-gray-500';
            $textColor = $isPastOrCurrent ? 'text-gray-900 dark:text-white font-bold' : 'text-gray-500 dark:text-gray-400 font-medium';
        @endphp
        
        <div class="relative flex items-center gap-4">
            <!-- Timeline Dot / Icon -->
            <div class="relative z-10 flex items-center justify-center w-5 h-5 rounded-full {{ $iconBg }} shadow ring-4 ring-white dark:ring-gray-900">
                <x-icon name="{{ $step['icon'] }}" class="w-3 h-3 {{ $iconColor }}" />
            </div>
            
            <!-- Content -->
            <div class="flex-1">
                <p class="{{ $textColor }} text-sm">
                    {{ $step['label'] }}
                </p>
                @if($isCurrent)
                    <p class="text-xs text-gray-500 mt-0.5">Current Status</p>
                @endif
                
                @if($key === 'interviewed' && $isPastOrCurrent)
                    @php 
                        $latestAssignment = $record->interviewAssignments()->latest()->first(); 
                    @endphp
                    @if($latestAssignment && $latestAssignment->overall_score !== null)
                        <p class="text-xs text-amber-600 font-medium mt-1">
                            Score: {{ $latestAssignment->overall_score }} / 50
                        </p>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
