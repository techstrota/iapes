<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
@php
    $indexUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('index');
    $viewUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('view', ['record' => $record->task_id]);
    $evalUrl = route('filament.admin.resources.task-management.task-submission-and-evaluations.index');
    
    $priority = strtolower($record->priority ?? 'medium');
    $status = $record->status ?? 'active';
    $dueDate = $record->due_date ? \Carbon\Carbon::parse($record->due_date) : null;
    $isOverdue = $dueDate && $dueDate->isPast() && $status !== 'completed';
    
    $assignedCount = $record->assigned_interns->count();
    $pendingSubmissionsCount = $record->submissions()->whereNull('evaluated_at')->count();
@endphp

<style>
    /* ── Stitch Edit Task Form Styling ── */
    .fi-resource-edit-record-page .fi-section {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
        overflow: hidden !important;
    }
    .fi-resource-edit-record-page .fi-section-header {
        background-color: #0b1326 !important;
        border-bottom: 1.5px solid #222a3d !important;
        padding: 14px 20px !important;
    }
    .fi-resource-edit-record-page .fi-section-header-heading {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
    }
    .fi-resource-edit-record-page .fi-section-header-description {
        color: #8e909f !important;
        font-size: 12px !important;
        margin-top: 2px !important;
    }
    .fi-resource-edit-record-page .fi-section-content {
        padding: 22px !important;
        background-color: #131b2e !important;
    }

    /* Labels & Field Wrappers */
    .fi-resource-edit-record-page .fi-fo-field-wrp-label label,
    .fi-resource-edit-record-page .fi-fo-field-wrp-label span {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #8e909f !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    /* Inputs, Textareas, Selects */
    .fi-resource-edit-record-page input[type="text"],
    .fi-resource-edit-record-page input[type="email"],
    .fi-resource-edit-record-page input[type="password"],
    .fi-resource-edit-record-page textarea,
    .fi-resource-edit-record-page select,
    .fi-resource-edit-record-page .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .fi-resource-edit-record-page .fi-input-wrp:focus-within,
    .fi-resource-edit-record-page input:focus,
    .fi-resource-edit-record-page textarea:focus,
    .fi-resource-edit-record-page select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
    }

    /* File Upload Drop Zone */
    .fi-resource-edit-record-page .fi-fo-file-upload {
        background-color: #090e1c !important;
        border: 1.5px dashed #222a3d !important;
        border-radius: 12px !important;
        transition: all 0.15s ease !important;
    }
    .fi-resource-edit-record-page .fi-fo-file-upload:hover {
        border-color: #3b82f6 !important;
        background-color: rgba(30, 64, 175, 0.05) !important;
    }

    /* Action Buttons */
    .fi-resource-edit-record-page .fi-form-actions {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 14px !important;
        padding: 14px 20px !important;
        margin-top: 20px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    }
    .fi-resource-edit-record-page .fi-form-actions button[type="submit"],
    .fi-resource-edit-record-page .fi-btn-color-primary {
        background-color: #1e40af !important;
        border: 1px solid #3b82f6 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
    }
    .fi-resource-edit-record-page .fi-btn-color-primary:hover {
        background-color: #1d4ed8 !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

    {{-- 1. Hero Context & Action Banner --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; position: relative; z-index: 1;">
            {{-- Left: Task Title & Badges --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                            Edit Task: {{ $record->title }}
                        </h1>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                        {{-- Priority Chip --}}
                        @if ($priority === 'high')
                            <span style="font-size: 11px; font-weight: 700; color: #ffb4ab; background-color: rgba(147, 0, 10, 0.35); border: 1px solid rgba(255, 180, 171, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                🔥 High Priority
                            </span>
                        @elseif ($priority === 'low')
                            <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(0, 49, 31, 0.4); border: 1px solid rgba(78, 222, 163, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                🟢 Low Priority
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; color: #c0c1ff; background-color: rgba(52, 51, 195, 0.25); border: 1px solid rgba(192, 193, 255, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                ⚡ Medium Priority
                            </span>
                        @endif

                        {{-- Due Date Chip --}}
                        @if (!$dueDate)
                            <span style="font-size: 11px; font-weight: 600; color: #94a3b8; background-color: #171f33; border: 1px solid #222a3d; padding: 2px 8px; border-radius: 9999px;">
                                No Due Date
                            </span>
                        @elseif ($isOverdue)
                            <span style="font-size: 11px; font-weight: 700; color: #ffb4ab; background-color: rgba(147, 0, 10, 0.35); border: 1px solid rgba(255, 180, 171, 0.35); padding: 2px 8px; border-radius: 9999px;">
                                ⚠️ Overdue ({{ $dueDate->format('M d') }})
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 600; color: #b8c4ff; background-color: #171f33; border: 1px solid #222a3d; padding: 2px 8px; border-radius: 9999px;">
                                📅 Due {{ $dueDate->format('M d, Y') }}
                            </span>
                        @endif

                        {{-- Scope Badge --}}
                        <span style="font-size: 11px; font-weight: 600; color: #dae2fd; background-color: #171f33; border: 1px solid #222a3d; padding: 2px 8px; border-radius: 9999px;">
                            👥 {{ $assignedCount }} Interns Assigned
                        </span>

                        {{-- Status Pill --}}
                        @if ($status === 'completed')
                            <span style="font-size: 11px; font-weight: 700; color: #4edea3; background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                ✓ Completed
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; color: #93c5fd; background-color: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 8px; border-radius: 9999px;">
                                ● Active
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Navigation & Actions Toolbar --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ $viewUrl }}"
                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Task Details
                </a>

                <a href="{{ $indexUrl }}"
                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Tasks Directory
                </a>

                <a href="{{ $evalUrl }}"
                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #171f33; color: #dae2fd; padding: 9px 15px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid #222a3d; transition: all 0.15s ease;">
                    <svg style="width: 15px; height: 15px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Evaluate ({{ $pendingSubmissionsCount }})
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Auto-Sync Alert Notice --}}
    <div style="background-color: rgba(30, 64, 175, 0.12); border: 1.5px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div style="font-size: 12.5px; color: #dae2fd;">
            <strong style="color: #ffffff;">Target Audience Synchronization:</strong>
            Modifying recipient selections below will dynamically re-synchronize individual intern assignments for this deliverable.
        </div>
    </div>

    {{-- 3. Form Container --}}
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

    <x-filament-panels::page.unsaved-data-changes-alert />
</div>
</x-filament-panels::page>
