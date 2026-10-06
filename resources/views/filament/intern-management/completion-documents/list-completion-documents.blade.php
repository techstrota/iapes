@php
    $viewData = $this->getViewData();
    $interns = $viewData['interns'];
    $batches = $viewData['batches'];
    $stats = $viewData['stats'];
@endphp

<x-filament-panels::page>
    <div class="stitch-dark-canvas" style="font-family: 'Plus Jakarta Sans', Inter, -apple-system, BlinkMacSystemFont, sans-serif; color: #dae2fd;">

        <style>
            .stitch-kpi-card {
                background-color: #131b2e;
                border: 1px solid #222a3d;
                border-radius: 14px;
                padding: 18px 20px;
                display: flex;
                align-items: center;
                gap: 16px;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
                transition: transform 0.15s ease, border-color 0.15s ease;
            }
            .stitch-kpi-card:hover {
                border-color: #374365;
            }
            .stitch-filter-bar {
                background-color: #131b2e;
                border: 1px solid #222a3d;
                border-radius: 14px;
                padding: 14px 18px;
                margin-bottom: 20px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
            }
            .stitch-select {
                border: 1px solid #222a3d;
                border-radius: 8px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                color: #dae2fd;
                background-color: #171f33;
                outline: none;
            }
            .stitch-select:focus, .stitch-input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
            }
            .stitch-input {
                width: 100%;
                border: 1px solid #222a3d;
                border-radius: 8px;
                padding: 7px 12px 7px 34px;
                font-size: 13px;
                color: #dae2fd;
                background-color: #171f33;
                outline: none;
            }
            .stitch-input::placeholder {
                color: #8e909f;
            }
            .stitch-card {
                background-color: #131b2e;
                border: 1px solid #222a3d;
                border-radius: 14px;
                padding: 18px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
                transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            }
            .stitch-card:hover {
                border-color: #3755c3;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
            }
            .stitch-badge-box {
                background-color: #0b1326;
                border: 1px solid #1e293b;
                border-radius: 10px;
                padding: 10px 12px;
                margin-bottom: 14px;
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .stitch-modal-overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                background-color: rgba(6, 14, 32, 0.85);
                backdrop-filter: blur(8px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }
            .stitch-modal-content {
                background-color: #131b2e;
                border-radius: 16px;
                width: 100%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
                border: 1px solid #222a3d;
                color: #dae2fd;
            }
            .stitch-form-input {
                width: 100%;
                border: 1px solid #222a3d;
                border-radius: 8px;
                padding: 8px 12px;
                font-size: 13px;
                color: #dae2fd;
                background-color: #171f33;
                outline: none;
            }
            .stitch-form-input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
            }
        </style>

        {{-- 1. Top KPI Statistics Row (Stitch Dark Theme) --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            {{-- Total Interns --}}
            <div class="stitch-kpi-card">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(30, 64, 175, 0.25); color: #b8c4ff; border: 1px solid rgba(55, 85, 195, 0.4); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Total Interns</div>
                    <div style="font-size: 28px; font-weight: 800; color: #dae2fd; line-height: 1.1; margin-top: 2px;">{{ $stats['total'] }}</div>
                </div>
            </div>

            {{-- Certificates Issued --}}
            <div class="stitch-kpi-card">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Certificates Issued</div>
                    <div style="font-size: 28px; font-weight: 800; color: #f59e0b; line-height: 1.1; margin-top: 2px;">{{ $stats['certs'] }}</div>
                </div>
            </div>

            {{-- Letters Issued --}}
            <div class="stitch-kpi-card">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(16, 185, 129, 0.18); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.35); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Letters Issued</div>
                    <div style="font-size: 28px; font-weight: 800; color: #4edea3; line-height: 1.1; margin-top: 2px;">{{ $stats['letters'] }}</div>
                </div>
            </div>

            {{-- Both Completed --}}
            <div class="stitch-kpi-card">
                <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(59, 130, 246, 0.18); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #8e909f; text-transform: uppercase;">Fully Documented</div>
                    <div style="font-size: 28px; font-weight: 800; color: #93c5fd; line-height: 1.1; margin-top: 2px;">{{ $stats['both'] }}</div>
                </div>
            </div>
        </div>

        {{-- 2. Filter & Search Controls Bar --}}
        <div class="stitch-filter-bar">
            {{-- Left: View Mode Pills --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                <div style="display: inline-flex; background-color: #0b1326; padding: 3px; border-radius: 10px; gap: 3px; border: 1px solid #222a3d;">
                    <button type="button" wire:click="$set('viewMode', 'all')"
                        style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'all' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 1px 3px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        All Statuses
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'current')"
                        style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'current' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 1px 3px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        Active Interns
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'archived')"
                        style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s ease; {{ $viewMode === 'archived' ? 'background-color: #1e40af; color: #FFFFFF; box-shadow: 0 1px 3px rgba(30,64,175,0.4);' : 'background-color: transparent; color: #8e909f;' }}">
                        Archived Interns
                    </button>
                </div>

                {{-- Document Status Dropdown --}}
                <select wire:model.live="docFilter" class="stitch-select">
                    <option value="all">All Documents</option>
                    <option value="has_cert">🏅 Certificate Issued</option>
                    <option value="no_cert">⚠️ Missing Certificate</option>
                    <option value="has_letter">📄 Letter Issued</option>
                    <option value="no_letter">⚠️ Missing Letter</option>
                </select>

                {{-- Cohort Batch Dropdown --}}
                <select wire:model.live="selectedBatchId" class="stitch-select">
                    <option value="">All Cohorts / Batches</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Right: Search Box & Per Page --}}
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px; max-width: 400px;">
                <div style="position: relative; width: 100%;">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, code, ref ID, role..." class="stitch-input" />
                    <svg style="position: absolute; left: 10px; top: 9px; width: 16px; height: 16px; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select wire:model.live="perPage" class="stitch-select">
                    <option value="6">6 / page</option>
                    <option value="12">12 / page</option>
                    <option value="24">24 / page</option>
                    <option value="48">48 / page</option>
                </select>
            </div>
        </div>

        {{-- 3. Intern Cards Grid --}}
        @if($interns->isEmpty())
            <div style="background-color: #131b2e; border: 1px dashed #222a3d; border-radius: 14px; padding: 48px; text-align: center;">
                <svg style="width: 48px; height: 48px; margin: 0 auto; color: #8e909f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div style="font-size: 16px; font-weight: 700; color: #dae2fd; margin-top: 12px;">No interns match the current filters</div>
                <div style="font-size: 13px; color: #8e909f; margin-top: 4px;">Try adjusting your search query, batch selection, or status filters.</div>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; margin-bottom: 24px;">
                @foreach($interns as $intern)
                    @php
                        $name = $intern->name ?: ($intern->offerletter?->name ?? $intern->application?->name ?? 'Intern');
                        $internCode = $intern->intern_code ?: ('INT-' . str_pad((string) $intern->id, 3, '0', STR_PAD_LEFT));
                        $role = $intern->internship_role ?: ($intern->offerletter?->internship_role ?? 'Software Development');
                        $batchName = $intern->batch?->batch_name ?: 'Batch not assigned';
                        $projectName = $intern->team?->team_name ?: ($intern->project_name ?: 'Project not assigned');
                        $hasCert = filled($intern->cert_ref_id) || $intern->completionCertificate()->exists();
                        $hasLetter = filled($intern->letter_ref_id) || $intern->completionLetter()->exists();
                        $certRef = $intern->cert_ref_id ?: $intern->completionCertificate?->cert_ref_id;
                        $letterRef = $intern->letter_ref_id ?: $intern->completionLetter?->letter_ref_id;

                        // Initials for avatar
                        $words = explode(' ', trim($name));
                        $initials = count($words) >= 2
                            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                            : strtoupper(substr($name, 0, 2));
                    @endphp

                    <div class="stitch-card">
                        <div>
                            {{-- Top Row: Code & Status --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                                <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; font-weight: 700; color: #b8c4ff; background-color: #171f33; border: 1px solid #222a3d; padding: 3px 8px; border-radius: 6px;">
                                    {{ $internCode }}
                                </span>
                                @if($intern->is_active)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                        <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #4edea3;"></span>
                                        Active
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; color: #8e909f; background-color: #171f33; border: 1px solid #222a3d; padding: 2px 8px; border-radius: 9999px;">
                                        <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #8e909f;"></span>
                                        Archived
                                    </span>
                                @endif
                            </div>

                            {{-- Intern Identity --}}
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                @if($intern->intern_image)
                                    <img src="{{ asset('storage/' . $intern->intern_image) }}" alt="{{ $name }}"
                                         style="width: 44px; height: 44px; border-radius: 9999px; object-fit: cover; border: 2px solid #222a3d; flex-shrink: 0;" />
                                @else
                                    <div style="width: 44px; height: 44px; border-radius: 9999px; background: linear-gradient(135deg, #1E40AF, #3755c3); color: #FFFFFF; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                                        {{ $initials }}
                                    </div>
                                @endif

                                <div style="min-width: 0; flex: 1;">
                                    <a href="{{ \App\Filament\Resources\InternManagement\InternResource::getUrl('view', ['record' => $intern]) }}"
                                       style="display: block; font-weight: 700; font-size: 15px; color: #dae2fd; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: color 0.15s ease;"
                                       onmouseover="this.style.color='#b8c4ff'" onmouseout="this.style.color='#dae2fd'">
                                        {{ $name }}
                                    </a>
                                    <div style="font-size: 12px; color: #8e909f; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $role }}
                                    </div>
                                </div>
                            </div>

                            {{-- Cohort & Project Tags --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px;">
                                <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background-color: rgba(30, 64, 175, 0.25); color: #b8c4ff; border: 1px solid rgba(55, 85, 195, 0.35);">
                                    {{ $batchName }}
                                </span>
                                <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background-color: #171f33; color: #c4c5d5; border: 1px solid #222a3d;">
                                    📁 {{ \Illuminate\Support\Str::limit($projectName, 22) }}
                                </span>
                            </div>

                            {{-- Document Status Badges --}}
                            <div class="stitch-badge-box">
                                {{-- Certificate Ref Badge --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                                    <span style="color: #8e909f; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                        <span>🏅</span> Certificate:
                                    </span>
                                    @if($hasCert)
                                        <span style="font-family: ui-monospace, SFMono-Regular, monospace; font-weight: 700; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 8px; border-radius: 6px;">
                                            {{ $certRef ?: ('CERT-' . $internCode) }}
                                        </span>
                                    @else
                                        <span style="font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #222a3d; padding: 2px 8px; border-radius: 6px;">
                                            Not Generated
                                        </span>
                                    @endif
                                </div>

                                {{-- Letter Ref Badge --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                                    <span style="color: #8e909f; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                        <span>📄</span> Letter:
                                    </span>
                                    @if($hasLetter)
                                        <span style="font-family: ui-monospace, SFMono-Regular, monospace; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 2px 8px; border-radius: 6px;">
                                            {{ $letterRef ?: ('LET-' . $internCode) }}
                                        </span>
                                    @else
                                        <span style="font-weight: 600; color: #8e909f; background-color: #171f33; border: 1px dashed #222a3d; padding: 2px 8px; border-radius: 6px;">
                                            Not Generated
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons: 1. Certificate  2. Letter --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; border-top: 1px solid #222a3d; padding-top: 12px;">
                            {{-- 1. Certificate Action --}}
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <button type="button" wire:click="openCertModal({{ $intern->id }})"
                                    style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 10px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.15s ease; {{ $hasCert ? 'background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);' : 'background-color: #d97706; color: #FFFFFF; border: none; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.3);' }}">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                    {{ $hasCert ? 'Certificate' : 'Generate Cert' }}
                                </button>
                                
                                @if($hasCert)
                                    <div style="display: flex; gap: 4px;">
                                        <a href="{{ route('intern.certificate.view', ['id' => $intern->id]) }}" target="_blank"
                                           style="flex: 1; text-align: center; font-size: 11px; font-weight: 600; color: #c4c5d5; background-color: #171f33; border: 1px solid #222a3d; padding: 3px; border-radius: 5px; text-decoration: none;">
                                            View
                                        </a>
                                        <a href="{{ route('intern.certificate.download', ['id' => $intern->id]) }}" target="_blank"
                                           style="flex: 1; text-align: center; font-size: 11px; font-weight: 600; color: #fbbf24; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 3px; border-radius: 5px; text-decoration: none;">
                                            PDF
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- 2. Letter Action --}}
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <button type="button" wire:click="openLetterModal({{ $intern->id }})"
                                    style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 10px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.15s ease; {{ $hasLetter ? 'background-color: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.35);' : 'background-color: #059669; color: #FFFFFF; border: none; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);' }}">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $hasLetter ? 'Letter' : 'Generate Letter' }}
                                </button>

                                @if($hasLetter)
                                    <div style="display: flex; gap: 4px;">
                                        <a href="{{ route('intern.completion_letter.view', ['id' => $intern->id]) }}" target="_blank"
                                           style="flex: 1; text-align: center; font-size: 11px; font-weight: 600; color: #c4c5d5; background-color: #171f33; border: 1px solid #222a3d; padding: 3px; border-radius: 5px; text-decoration: none;">
                                            View
                                        </a>
                                        <a href="{{ route('intern.completion_letter.download', ['id' => $intern->id]) }}" target="_blank"
                                           style="flex: 1; text-align: center; font-size: 11px; font-weight: 600; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(78, 222, 163, 0.3); padding: 3px; border-radius: 5px; text-decoration: none;">
                                            PDF
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div style="margin-top: 16px;">
                {{ $interns->links() }}
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- MODAL 1: Completion Certificate Generation & Verification --}}
        {{-- ========================================================= --}}
        @if($showCertModal)
            <div class="stitch-modal-overlay">
                <div class="stitch-modal-content" style="max-width: 640px;">
                    
                    {{-- Modal Header --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; background-color: #171f33; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; color: #fbbf24; margin: 0;">Completion Certificate</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">Verify or modify details before certificate generation</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCertModal" style="border: none; background: transparent; cursor: pointer; color: #8e909f; font-size: 22px; font-weight: 700;" onmouseover="this.style.color='#dae2fd'" onmouseout="this.style.color='#8e909f'">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                        {{-- Readonly Intern Identity Pill --}}
                        <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px;">
                            <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Intern Information</div>
                            <div style="font-size: 14px; font-weight: 700; color: #dae2fd;">{{ $certForm['intern_name'] ?? '' }} ({{ $certForm['intern_code'] ?? '' }})</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">Assigned Role: <strong style="color: #b8c4ff;">{{ $certForm['internship_role'] ?? 'Software Development' }}</strong></div>
                        </div>

                        {{-- Grid of Form Inputs --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div style="grid-column: span 2;">
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Project Name *</label>
                                <input type="text" wire:model="certForm.project_name" required class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Awarded Grade *</label>
                                <input type="text" wire:model="certForm.grade" placeholder="e.g. A+, A, O" required class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Certificate Issuing Date *</label>
                                <input type="date" wire:model="certForm.issuing_date" required class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Joining Date</label>
                                <input type="date" wire:model="certForm.joining_date" class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Completion Date</label>
                                <input type="date" wire:model="certForm.completion_date" class="stitch-form-input" />
                            </div>

                            <div style="grid-column: span 2;">
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Internship Role (Printed on Certificate)</label>
                                <input type="text" wire:model="certForm.internship_role" class="stitch-form-input" />
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: flex-end; gap: 10px; background-color: #171f33; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" wire:click="closeCertModal"
                            style="padding: 8px 16px; font-size: 13px; font-weight: 700; color: #c4c5d5; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="button" wire:click="saveCertificate"
                            style="padding: 8px 20px; font-size: 13px; font-weight: 700; color: #FFFFFF; background-color: #d97706; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.4);">
                            Generate Certificate
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- MODAL 2: Completion Letter Generation & Verification      --}}
        {{-- ========================================================= --}}
        @if($showLetterModal)
            <div class="stitch-modal-overlay">
                <div class="stitch-modal-content" style="max-width: 680px;">
                    
                    {{-- Modal Header --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid #222a3d; display: flex; align-items: center; justify-content: space-between; background-color: #171f33; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background-color: rgba(16, 185, 129, 0.2); color: #4edea3; border: 1px solid rgba(78, 222, 163, 0.35); display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; color: #4edea3; margin: 0;">Completion Letter</h3>
                                <p style="font-size: 12px; color: #8e909f; margin: 2px 0 0 0;">Verify or modify letter parameters before document generation</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeLetterModal" style="border: none; background: transparent; cursor: pointer; color: #8e909f; font-size: 22px; font-weight: 700;" onmouseover="this.style.color='#dae2fd'" onmouseout="this.style.color='#8e909f'">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                        {{-- Readonly Intern Identity Pill --}}
                        <div style="background-color: #0b1326; border: 1px solid #222a3d; border-radius: 10px; padding: 12px 16px;">
                            <div style="font-size: 11px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Candidate Academic Profile</div>
                            <div style="font-size: 14px; font-weight: 700; color: #dae2fd;">{{ $letterForm['intern_name'] ?? '' }} ({{ $letterForm['intern_code'] ?? '' }})</div>
                            <div style="font-size: 12px; color: #8e909f; margin-top: 2px;">
                                <strong style="color: #4edea3;">{{ $letterForm['degree'] ?? 'Degree' }}</strong> · {{ $letterForm['college'] ?: ($letterForm['university'] ?: 'Institution') }}
                            </div>
                        </div>

                        {{-- Grid of Form Inputs --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Letter Template *</label>
                                <select wire:model="letterForm.template" required class="stitch-form-input">
                                    <option value="bachelors">Bachelor Degree Completion Letter</option>
                                    <option value="masters">Master Degree Completion Letter</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Awarded Grade</label>
                                <input type="text" wire:model="letterForm.grade" placeholder="e.g. A+, A, O" class="stitch-form-input" />
                            </div>

                            <div style="grid-column: span 2;">
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Assigned Project Title *</label>
                                <input type="text" wire:model="letterForm.project_name" required class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Joining Date</label>
                                <input type="date" wire:model="letterForm.joining_date" class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Completion Date</label>
                                <input type="date" wire:model="letterForm.completion_date" class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Letter Issuing Date *</label>
                                <input type="date" wire:model="letterForm.issuing_date" required class="stitch-form-input" />
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Working Hours Schedule</label>
                                <input type="text" wire:model="letterForm.working_hours" placeholder="e.g. 42 hours per week" class="stitch-form-input" />
                            </div>

                            <div style="grid-column: span 2;">
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Course / Role Title</label>
                                <input type="text" wire:model="letterForm.internship_role" class="stitch-form-input" />
                            </div>

                            <div style="grid-column: span 2;">
                                <label style="display: block; font-size: 12px; font-weight: 700; color: #c4c5d5; margin-bottom: 6px;">Project / Skills Deliverables Description (HTML or Plain Text)</label>
                                <textarea wire:model="letterForm.project_description" rows="3" placeholder="Bullet points of key competencies and project achievements"
                                    class="stitch-form-input" style="font-family: monospace;"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 16px 24px; border-top: 1px solid #222a3d; display: flex; align-items: center; justify-content: flex-end; gap: 10px; background-color: #171f33; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" wire:click="closeLetterModal"
                            style="padding: 8px 16px; font-size: 13px; font-weight: 700; color: #c4c5d5; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 8px; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="button" wire:click="saveLetter"
                            style="padding: 8px 20px; font-size: 13px; font-weight: 700; color: #FFFFFF; background-color: #059669; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.4);">
                            Generate Letter
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
