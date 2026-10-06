<x-filament-panels::page>
@php
    $name = $record->name ?: ($record->offerletter?->name ?? $record->application?->name ?? 'Unnamed Intern');
    $internCode = $record->intern_code ?? ('INT-' . str_pad($record->id, 3, '0', STR_PAD_LEFT));
    $role = $record->internship_role ?: ($record->offerletter?->internship_role ?? $record->application?->domain ?? $record->domain ?? 'Intern');
    $institution = $record->university ?: ($record->college ?: ($record->offerletter?->university ?? $record->offerletter?->college ?? 'Institution Not Recorded'));
    $batchDisplay = $record->batch?->batch_name ?? $record->batch?->name ?? 'No Batch';
    $projectDisplay = $record->team?->team_name ?: ($record->project_name ?: 'No Project');
    
    $viewUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('view', ['record' => $record]);
    $indexUrl = \App\Filament\Resources\InternManagement\InternResource::getUrl('index');
    
    // Initials fallback
    $words = explode(' ', trim($name));
    $initials = strtoupper(substr($words[0] ?? 'I', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (empty($initials)) $initials = 'IN';
@endphp

<style>
    /* ── Edit Intern Form Custom Stitch Theme ── */
    .fi-fo-tabs {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
        overflow: hidden !important;
    }

    /* Tabs Bar Track */
    .fi-fo-tabs > nav.fi-tabs,
    .fi-tabs.fi-contained,
    .fi-tabs {
        background-color: #0b1326 !important;
        border-bottom: 1.5px solid #222a3d !important;
        padding: 8px 12px !important;
        gap: 6px !important;
    }

    /* Tab Items */
    .fi-tabs-item {
        border-radius: 10px !important;
        padding: 9px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        border: 1px solid transparent !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background-color: transparent !important;
    }

    .fi-tabs-item:hover {
        background-color: #17223b !important;
        color: #dae2fd !important;
        border-color: #222a3d !important;
    }

    /* Active Tab */
    .fi-tabs-item.fi-active,
    .fi-tabs-item[aria-selected="true"] {
        background-color: #1e40af !important;
        color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        font-weight: 700 !important;
    }

    .fi-tabs-item.fi-active .fi-tabs-item-icon,
    .fi-tabs-item[aria-selected="true"] .fi-tabs-item-icon,
    .fi-tabs-item.fi-active svg,
    .fi-tabs-item[aria-selected="true"] svg {
        color: #ffffff !important;
    }

    .fi-tabs-item .fi-tabs-item-icon,
    .fi-tabs-item svg {
        color: #8e909f !important;
        transition: color 0.18s ease !important;
    }

    .fi-tabs-item:hover .fi-tabs-item-icon,
    .fi-tabs-item:hover svg {
        color: #60a5fa !important;
    }

    /* Tab Panel Content */
    .fi-fo-tabs-tab {
        padding: 24px !important;
        background-color: #131b2e !important;
    }

    /* Field Labels */
    .fi-fo-field-wrp-label label,
    .fi-fo-field-wrp-label span {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #8e909f !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    /* Input Wrappers */
    .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        outline: none !important;
        --tw-ring-color: transparent !important;
        --tw-ring-shadow: none !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
    }

    .fi-input-wrp input,
    .fi-input-wrp select,
    .fi-input-wrp textarea {
        color: #dae2fd !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        background-color: transparent !important;
    }

    .fi-input-wrp input::placeholder,
    .fi-input-wrp textarea::placeholder {
        color: #64748b !important;
    }

    /* FileUpload Circle */
    .filepond--panel-root {
        background-color: #090e1c !important;
        border: 2px dashed #222a3d !important;
        border-radius: 50% !important;
    }

    .filepond--drop-label {
        color: #8e909f !important;
        font-size: 12px !important;
    }

    /* Toggle Switch */
    .fi-toggle[aria-checked="true"],
    button.fi-toggle[aria-checked="true"] {
        background-color: #10b981 !important;
    }

    .fi-toggle[aria-checked="false"],
    button.fi-toggle[aria-checked="false"] {
        background-color: #222a3d !important;
    }

    .fi-fo-field-wrp-helper-text {
        color: #8e909f !important;
        font-size: 12px !important;
        margin-top: 5px !important;
    }

    /* Rich Editor */
    .fi-fo-rich-editor {
        background-color: #090e1c !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }

    .fi-fo-rich-editor-toolbar {
        background-color: #0b1326 !important;
        border-bottom: 1px solid #222a3d !important;
    }

    .fi-fo-rich-editor-toolbar button {
        color: #8e909f !important;
    }

    .fi-fo-rich-editor-toolbar button:hover {
        color: #dae2fd !important;
        background-color: #17223b !important;
    }

    .trix-content {
        color: #dae2fd !important;
        min-height: 140px !important;
        background-color: #090e1c !important;
        padding: 14px !important;
    }

    /* Form Action Buttons */
    .fi-form-actions button[type="submit"],
    .fi-btn-color-primary {
        background: #1e40af !important;
        border: 1px solid #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 9px !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        padding: 9px 22px !important;
        font-size: 13.5px !important;
        transition: all 0.15s ease !important;
    }

    .fi-form-actions button[type="submit"]:hover,
    .fi-btn-color-primary:hover {
        background: #1d4ed8 !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 4px 14px rgba(30, 64, 175, 0.6) !important;
    }

    .fi-btn-color-gray {
        background: #171f33 !important;
        border: 1px solid #222a3d !important;
        color: #dae2fd !important;
        font-weight: 600 !important;
        border-radius: 9px !important;
        padding: 9px 20px !important;
        font-size: 13.5px !important;
        transition: all 0.15s ease !important;
    }

    .fi-btn-color-gray:hover {
        background: #1e293b !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd; display: flex; flex-direction: column; gap: 20px;">

    {{-- Top Navigation & Sync Notification Bar --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ $viewUrl }}"
               style="display: inline-flex; align-items: center; gap: 6px; color: #8e909f; text-decoration: none; font-size: 13px; font-weight: 600; padding: 7px 14px; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 10px; transition: all 0.2s ease;"
               onmouseover="this.style.color='#dae2fd'; this.style.borderColor='#3b82f6';"
               onmouseout="this.style.color='#8e909f'; this.style.borderColor='#222a3d';">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Profile View</span>
            </a>

            <a href="{{ $indexUrl }}"
               style="display: inline-flex; align-items: center; gap: 6px; color: #8e909f; text-decoration: none; font-size: 13px; font-weight: 600; padding: 7px 14px; background-color: #131b2e; border: 1px solid #222a3d; border-radius: 10px; transition: all 0.2s ease;"
               onmouseover="this.style.color='#dae2fd'; this.style.borderColor='#3b82f6';"
               onmouseout="this.style.color='#8e909f'; this.style.borderColor='#222a3d';">
                <span>Directory</span>
            </a>
        </div>

        {{-- Auto-sync notice pill --}}
        <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #4edea3; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); padding: 5px 12px; border-radius: 8px;">
            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Changes automatically synchronize with Application & Offer Letter</span>
        </div>
    </div>

    {{-- Hero Summary Card --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 20px 24px; box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px; min-width: 260px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; overflow: hidden; background-color: #0b1326; border: 2px solid #1e40af; display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 20px; font-weight: 800; flex-shrink: 0; box-shadow: 0 2px 10px rgba(30,64,175,0.4);">
                @if ($record->intern_image)
                    <img src="{{ asset('storage/' . $record->intern_image) }}" alt="{{ $name }}" style="width: 100%; height: 100%; object-fit: cover;" />
                @else
                    {{ $initials }}
                @endif
            </div>

            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">{{ $name }}</h2>
                    <span style="font-family: ui-monospace, monospace; font-size: 12px; font-weight: 700; color: #60a5fa; background-color: #0b1326; border: 1px solid #1d4ed8; padding: 2px 7px; border-radius: 6px;">
                        {{ $internCode }}
                    </span>
                    <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; {{ $record->is_active ? 'background: rgba(16, 185, 129, 0.15); color: #4edea3; border: 1px solid rgba(16, 185, 129, 0.3);' : 'background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);' }}">
                        {{ $record->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div style="font-size: 13px; color: #94a3b8; margin-top: 3px; font-weight: 500;">
                    {{ $role }} • {{ $institution }}
                </div>
            </div>
        </div>

        {{-- Right Quick Actions --}}
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ $viewUrl }}"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 700; background-color: #1e40af; color: #ffffff; text-decoration: none; border: 1px solid #2563eb; box-shadow: 0 2px 8px rgba(30,64,175,0.4); transition: all 0.15s ease;"
               onmouseover="this.style.backgroundColor='#1d4ed8';"
               onmouseout="this.style.backgroundColor='#1e40af';">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Profile ↗
            </a>

            <button type="button"
                    wire:click="mountAction('changePassword')"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 600; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; cursor: pointer; transition: all 0.15s ease;"
                    onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#3b82f6';"
                    onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='#222a3d';">
                <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Change Password
            </button>

            <a href="{{ route('print-id-card', ['id' => $record->id]) }}" target="_blank"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 600; background-color: #171f33; color: #dae2fd; border: 1px solid #222a3d; text-decoration: none; transition: all 0.15s ease;"
               onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#3b82f6';"
               onmouseout="this.style.backgroundColor='#171f33'; this.style.borderColor='#222a3d';">
                <svg style="width: 14px; height: 14px; color: #a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                Print I-Card ↗
            </a>
        </div>
    </div>

    {{-- Main Edit Form Container --}}
    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

</div>
</x-filament-panels::page>
