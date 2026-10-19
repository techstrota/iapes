@php
    $modalBatch = \App\Models\InternManagement\InternshipBatch::with(['interns', 'interns.offerletter', 'interns.team'])->find($selectedBatchId);
    $batchInterns = $modalBatch?->interns ?? collect();
    $activeInterns = $batchInterns->where('is_active', true);
@endphp

@if ($showEmailModal && $modalBatch)
    <div style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background-color: rgba(5, 9, 20, 0.75); backdrop-filter: blur(8px); padding: 20px; box-sizing: border-box;">
        
        {{-- Modal Box --}}
        <div style="background-color: #0c1424; border: 1.5px solid #1e2c44; border-radius: 20px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7); overflow: hidden; animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
            
            {{-- Modal Header --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid #1a2538; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(180deg, #101a2f 0%, #0c1424 100%);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-size: 17px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.3;">
                            Send Batch Allocation Email
                        </h2>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                            Batch: <strong style="color: #60a5fa;">{{ $modalBatch->batch_name }}</strong> • Timing: <span style="color: #fbbf24;">{{ $modalBatch->batch_timing ?: 'Not specified' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Close Button --}}
                <button
                    type="button"
                    wire:click="closeEmailModal"
                    style="background: transparent; border: none; color: #64748b; cursor: pointer; padding: 6px; border-radius: 8px; transition: color 0.15s ease;"
                    onmouseover="this.style.color='#ffffff';"
                    onmouseout="this.style.color='#64748b';">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div style="padding: 22px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px;">
                
                {{-- 1. Mode Selection: Bulk vs Single Intern --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                        Select Sending Mode:
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        {{-- Bulk Option --}}
                        <label
                            wire:click="$set('emailMode', 'bulk')"
                            style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; {{ $emailMode === 'bulk' ? 'background: rgba(30, 64, 175, 0.25); border: 1.5px solid #3b82f6;' : 'background: #090e1c; border: 1.5px solid #1a2438;' }}">
                            <input type="radio" name="emailMode" value="bulk" wire:model.live="emailMode" style="accent-color: #3b82f6;" />
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700; color: {{ $emailMode === 'bulk' ? '#93c5fd' : '#ffffff' }};">
                                    Bulk Send (Batch-wise)
                                </div>
                                <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                    Send to all (or multiple) enrolled interns
                                </div>
                            </div>
                        </label>

                        {{-- Single Option --}}
                        <label
                            wire:click="$set('emailMode', 'single')"
                            style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s ease; {{ $emailMode === 'single' ? 'background: rgba(30, 64, 175, 0.25); border: 1.5px solid #3b82f6;' : 'background: #090e1c; border: 1.5px solid #1a2438;' }}">
                            <input type="radio" name="emailMode" value="single" wire:model.live="emailMode" style="accent-color: #3b82f6;" />
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700; color: {{ $emailMode === 'single' ? '#93c5fd' : '#ffffff' }};">
                                    Single Intern
                                </div>
                                <div style="font-size: 11px; color: #8e909f; margin-top: 1px;">
                                    Send to one specific intern only
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. Recipient Selection Section --}}
                @if ($activeInterns->isEmpty())
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 14px 16px; color: #fde68a; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                        <span>⚠️</span>
                        <span>There are no active interns enrolled in this batch yet. Assign interns to this batch before sending emails.</span>
                    </div>
                @else
                    {{-- Bulk Mode: Checkbox List --}}
                    @if ($emailMode === 'bulk')
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <label style="font-size: 11.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Recipients ({{ count($selectedInternIds) }} of {{ $activeInterns->count() }} selected):
                                </label>
                                <button
                                    type="button"
                                    wire:click="toggleSelectAll"
                                    style="background: transparent; border: none; color: #60a5fa; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    {{ count($selectedInternIds) === $activeInterns->count() ? 'Deselect All' : 'Select All' }}
                                </button>
                            </div>

                            <div style="background: #090e1c; border: 1px solid #1a2438; border-radius: 12px; max-height: 180px; overflow-y: auto; padding: 6px;">
                                @foreach ($activeInterns as $intern)
                                    @php
                                        $hasEmail = filled($intern->email);
                                        $duration = $intern->offerletter?->duration_label ?: '3 Months';
                                        $dates = ($intern->joining_date && $intern->completion_date)
                                            ? \Carbon\Carbon::parse($intern->joining_date)->format('d/m/y') . ' - ' . \Carbon\Carbon::parse($intern->completion_date)->format('d/m/y')
                                            : $duration;
                                    @endphp
                                    <label style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 10px; border-radius: 8px; cursor: pointer; transition: background 0.1s ease;"
                                           onmouseover="this.style.backgroundColor='#111b2e';"
                                           onmouseout="this.style.backgroundColor='transparent';">
                                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                            <input
                                                type="checkbox"
                                                value="{{ $intern->id }}"
                                                wire:model.live="selectedInternIds"
                                                {{ !$hasEmail ? 'disabled' : '' }}
                                                style="accent-color: #3b82f6; width: 16px; height: 16px;" />
                                            <div style="min-width: 0;">
                                                <div style="font-size: 13px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $intern->name }}
                                                    <span style="font-family: ui-monospace, monospace; font-size: 11px; font-weight: 600; color: #60a5fa; background: #0c1424; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">
                                                        {{ $intern->intern_code ?: ('INT-' . $intern->id) }}
                                                    </span>
                                                </div>
                                                <div style="font-size: 11.5px; color: {{ $hasEmail ? '#94a3b8' : '#f87171' }}; margin-top: 1px;">
                                                    {{ $hasEmail ? $intern->email : '⚠️ Missing Email Address' }}
                                                </div>
                                            </div>
                                        </div>
                                        <span style="font-size: 11px; color: #64748b; flex-shrink: 0;">
                                            🗓️ {{ $dates }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    {{-- Single Mode: Select Dropdown --}}
                    @else
                        <div>
                            <label style="display: block; font-size: 11.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                Choose Intern:
                            </label>
                            <select
                                wire:model.live="singleInternId"
                                style="width: 100%; background: #090e1c; border: 1.5px solid #1a2438; border-radius: 10px; padding: 10px 14px; font-size: 13.5px; color: #ffffff; outline: none;">
                                <option value="">-- Select an intern from this batch --</option>
                                @foreach ($activeInterns as $intern)
                                    <option value="{{ $intern->id }}">
                                        {{ $intern->name }} ({{ $intern->intern_code ?: 'INT-'.$intern->id }} • {{ $intern->email ?: 'No Email' }})
                                    </option>
                                @endforeach
                            </select>

                            @if ($singleInternId)
                                @php
                                    $selectedSingle = $activeInterns->firstWhere('id', (int) $singleInternId);
                                @endphp
                                @if ($selectedSingle)
                                    <div style="margin-top: 10px; background: #090e1c; border: 1px solid #1e2c44; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
                                        <div>
                                            <div style="font-size: 13px; font-weight: 700; color: #ffffff;">
                                                {{ $selectedSingle->name }}
                                            </div>
                                            <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                                                Email: <span style="color: #60a5fa;">{{ $selectedSingle->email ?: 'Not provided' }}</span>
                                            </div>
                                        </div>
                                        <div style="text-align: right; font-size: 12px; color: #cbd5e1;">
                                            ID: <strong style="color: #38bdf8;">{{ $selectedSingle->intern_code ?: 'INT-'.$selectedSingle->id }}</strong>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                @endif

                {{-- 3. Email Subject Input --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                        Email Subject:
                    </label>
                    <input
                        type="text"
                        wire:model="customSubject"
                        placeholder="Techstrota Internship — Batch Allocation & Schedule Details"
                        style="width: 100%; box-sizing: border-box; background: #090e1c; border: 1.5px solid #1a2438; border-radius: 10px; padding: 10px 14px; font-size: 13.5px; color: #ffffff; outline: none; transition: border-color 0.15s ease;"
                        onfocus="this.style.borderColor='#3b82f6';"
                        onblur="this.style.borderColor='#1a2438';" />
                </div>

                {{-- 4. Optional Custom Instructions / Notes --}}
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 700; color: #8e909f; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                        Additional Instructions / Notes (Optional):
                    </label>
                    <textarea
                        wire:model="customNote"
                        rows="3"
                        placeholder="e.g. Please bring your laptop for onboarding. Report to Meeting Room 2 at 09:30 AM sharp..."
                        style="width: 100%; box-sizing: border-box; background: #090e1c; border: 1.5px solid #1a2438; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #ffffff; outline: none; resize: vertical; transition: border-color 0.15s ease;"
                        onfocus="this.style.borderColor='#3b82f6';"
                        onblur="this.style.borderColor='#1a2438';"></textarea>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        These notes will be highlighted inside a special callout card in the email.
                    </div>
                </div>

            </div>

            {{-- Modal Footer Actions --}}
            <div style="padding: 16px 24px; border-top: 1px solid #1a2538; background: #080d19; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <button
                    type="button"
                    wire:click="closeEmailModal"
                    style="background: #111a2c; color: #94a3b8; border: 1px solid #1e2c44; border-radius: 10px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease;"
                    onmouseover="this.style.color='#ffffff'; this.style.borderColor='#334155';"
                    onmouseout="this.style.color='#94a3b8'; this.style.borderColor='#1e2c44';">
                    Cancel
                </button>

                @php
                    $recipientsCount = $emailMode === 'bulk' ? count($selectedInternIds) : ($singleInternId ? 1 : 0);
                @endphp

                <button
                    type="button"
                    wire:click="sendBatchEmail"
                    wire:loading.attr="disabled"
                    {{ $recipientsCount === 0 ? 'disabled' : '' }}
                    style="background: {{ $recipientsCount > 0 ? 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)' : '#1e293b' }}; color: {{ $recipientsCount > 0 ? '#ffffff' : '#64748b' }}; border: none; border-radius: 10px; padding: 9px 22px; font-size: 13.5px; font-weight: 700; cursor: {{ $recipientsCount > 0 ? 'pointer' : 'not-allowed' }}; display: inline-flex; align-items: center; gap: 8px; box-shadow: {{ $recipientsCount > 0 ? '0 4px 15px rgba(30, 64, 175, 0.4)' : 'none' }};">
                    <span wire:loading.remove wire:target="sendBatchEmail">
                        🚀 Send Email ({{ $recipientsCount }})
                    </span>
                    <span wire:loading wire:target="sendBatchEmail" style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg class="animate-spin" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending Emails...
                    </span>
                </button>
            </div>

        </div>

    </div>
@endif
