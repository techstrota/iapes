<x-filament-widgets::widget>
@php
    $data        = $this->getViewData();
    $progress    = $data['progress'];
    $elapsed     = $data['elapsed_days'];
    $left        = $data['days_left'];
    $total       = $data['total_days'];
    $startDate   = $data['start_date'];
    $endDate     = $data['end_date'];

    // Arc path for SVG donut
    $radius = 36;
    $circumference = 2 * M_PI * $radius;
    $dashOffset = $circumference - ($progress / 100) * $circumference;
@endphp

<style>
.ipw-card {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    padding: 22px;
    background-color: #131b2e;
    border: 1.5px solid #222a3d;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    height: 100%; 
    min-height: 380px; 
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.ipw-card::before {
    content:'';
    position:absolute;
    top:-60px; right:-60px;
    width:180px; height:180px;
    border-radius:50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, transparent 65%);
    pointer-events:none;
}
.ipw-top {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
}
.ipw-donut {
    position: relative;
    width: 90px;
    height: 90px;
    flex-shrink: 0;
}
.ipw-donut svg {
    width: 90px;
    height: 90px;
    transform: rotate(-90deg);
}
.ipw-donut-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.ipw-donut-pct {
    font-size: 21px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.03em;
    font-variant-numeric: tabular-nums;
}
.ipw-donut-sub {
    font-size: 10px;
    font-weight: 700;
    color: #60a5fa;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 3px;
}
.ipw-info {
    flex: 1;
}
.ipw-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.ipw-icon-wrap {
    width: 26px; height: 26px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(59, 130, 246, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.3);
    flex-shrink: 0;
}
.ipw-title {
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.01em;
}
.ipw-subtitle {
    font-size: 11px;
    color: #8e909f;
    font-weight: 500;
    margin-bottom: 12px;
}
.ipw-bar-wrap {
    position: relative;
    height: 6px;
    border-radius: 99px;
    background: rgba(255,255,255,0.08);
    overflow: visible;
}
.ipw-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    position: relative;
    transition: width 1s ease;
    min-width: {{ $progress > 0 ? '8px' : '0' }};
}
.ipw-bar-fill::after {
    content: '';
    position: absolute;
    right: -1px; top: 50%;
    transform: translateY(-50%);
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #93c5fd;
    border: 2px solid #0b1326;
    display: {{ $progress > 0 ? 'block' : 'none' }};
}
.ipw-bar-pct {
    font-size: 10px;
    font-weight: 600;
    color: #93c5fd;
    margin-top: 6px;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.ipw-stats {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}
.ipw-stat {
    border-radius: 12px;
    padding: 12px 10px 10px;
    background: #090e1c;
    border: 1px solid #222a3d;
}
.ipw-stat.highlight {
    background: rgba(30, 64, 175, 0.2);
    border-color: rgba(59, 130, 246, 0.4);
}
.ipw-stat-key {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8e909f;
    margin-bottom: 6px;
}
.ipw-stat.highlight .ipw-stat-key { color: #60a5fa; }
.ipw-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.03em;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.ipw-stat.highlight .ipw-stat-val { color: #ffffff; }
.ipw-stat-unit {
    font-size: 10px;
    color: #8e909f;
    margin-top: 4px;
}
.ipw-stat.highlight .ipw-stat-unit { color: #93c5fd; }
.ipw-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #222a3d;
}
.ipw-date-block .ipw-date-key {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8e909f;
    margin-bottom: 2px;
}
.ipw-date-block .ipw-date-val {
    font-size: 12px;
    font-weight: 600;
    color: #cbd5e1;
}
.ipw-date-block.end .ipw-date-val { color: #60a5fa; }
.ipw-timeline {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 12px;
}
.ipw-tl-line { flex:1; height:1px; background:#222a3d; }
.ipw-tl-dot { width:6px; height:6px; border-radius:50%; background:#3b82f6; flex-shrink:0; box-shadow:0 0 8px #3b82f6; }
</style>

<div class="ipw-card">

    {{-- Top: donut + title + bar --}}
    <div class="ipw-top">

        {{-- Donut --}}
        <div class="ipw-donut">
            <svg viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Track --}}
                <circle cx="44" cy="44" r="{{ $radius }}"
                        stroke="rgba(255,255,255,0.06)"
                        stroke-width="7"
                        fill="none" />
                {{-- Progress arc --}}
                <circle cx="44" cy="44" r="{{ $radius }}"
                        stroke="url(#donutGrad)"
                        stroke-width="7"
                        fill="none"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $dashOffset }}" />
                <defs>
                    <linearGradient id="donutGrad" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#3b82f6"/>
                        <stop offset="100%" stop-color="#60a5fa"/>
                    </linearGradient>
                </defs>
            </svg>
            <div class="ipw-donut-label">
                <span class="ipw-donut-pct">{{ $progress }}<span style="font-size:12px;color:#60a5fa;">%</span></span>
                <span class="ipw-donut-sub">done</span>
            </div>
        </div>

        {{-- Right of donut: title + bar --}}
        <div class="ipw-info">
            <div class="ipw-title-row">
                <div class="ipw-icon-wrap">
                    <x-heroicon-m-rocket-launch class="w-3.5 h-3.5 text-blue-400" />
                </div>
                <span class="ipw-title">Internship Milestone</span>
            </div>
            <p class="ipw-subtitle">{{ $data['status_message'] }}</p>

            <div class="ipw-bar-wrap">
                <div class="ipw-bar-fill" style="width:{{ max($progress, 0) }}%;"></div>
            </div>
            <p class="ipw-bar-pct">{{ $elapsed }} of {{ $total }} days elapsed</p>
        </div>
    </div>

    {{-- Stat boxes --}}
    <div class="ipw-stats">
        <div class="ipw-stat">
            <p class="ipw-stat-key">Elapsed</p>
            <p class="ipw-stat-val">{{ $elapsed }}</p>
            <p class="ipw-stat-unit">days</p>
        </div>
        <div class="ipw-stat highlight">
            <p class="ipw-stat-key">Remaining</p>
            <p class="ipw-stat-val">{{ $left }}</p>
            <p class="ipw-stat-unit">days left</p>
        </div>
        <div class="ipw-stat">
            <p class="ipw-stat-key">Duration</p>
            <p class="ipw-stat-val">{{ $total }}</p>
            <p class="ipw-stat-unit">days</p>
        </div>
    </div>

    {{-- Date footer --}}
    <div class="ipw-footer">
        <div class="ipw-date-block">
            <p class="ipw-date-key">Start Date</p>
            <p class="ipw-date-val">{{ $startDate }}</p>
        </div>
        <div class="ipw-timeline">
            <div class="ipw-tl-line"></div>
            <div class="ipw-tl-dot"></div>
            <div class="ipw-tl-line"></div>
        </div>
        <div class="ipw-date-block end" style="text-align:right;">
            <p class="ipw-date-key">Target End</p>
            <p class="ipw-date-val">{{ $endDate }}</p>
        </div>
    </div>

</div>
</x-filament-widgets::widget>