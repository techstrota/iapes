<x-filament-panels::page
    @class([
        'fi-resource-create-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
@php
    $indexUrl = \App\Filament\Resources\TaskManagement\TaskResource::getUrl('index');
    $evalUrl = route('filament.admin.resources.task-management.task-submission-and-evaluations.index');
@endphp

<style>
    /* ── Stitch Create Task Form Styling ── */
    .fi-resource-create-record-page .fi-section {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
        overflow: hidden !important;
    }
    .fi-resource-create-record-page .fi-section-header {
        background-color: #0b1326 !important;
        border-bottom: 1.5px solid #222a3d !important;
        padding: 14px 20px !important;
    }
    .fi-resource-create-record-page .fi-section-header-heading {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
    }
    .fi-resource-create-record-page .fi-section-header-description {
        color: #8e909f !important;
        font-size: 12px !important;
        margin-top: 2px !important;
    }
    .fi-resource-create-record-page .fi-section-content {
        padding: 22px !important;
        background-color: #131b2e !important;
    }

    /* Labels & Field Wrappers */
    .fi-resource-create-record-page .fi-fo-field-wrp-label label,
    .fi-resource-create-record-page .fi-fo-field-wrp-label span {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #8e909f !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    /* Inputs, Textareas, Selects */
    .fi-resource-create-record-page input[type="text"],
    .fi-resource-create-record-page input[type="email"],
    .fi-resource-create-record-page input[type="password"],
    .fi-resource-create-record-page textarea,
    .fi-resource-create-record-page select,
    .fi-resource-create-record-page .fi-input-wrp {
        background-color: #090e1c !important;
        border: 1px solid #222a3d !important;
        border-radius: 10px !important;
        color: #dae2fd !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .fi-resource-create-record-page .fi-input-wrp:focus-within,
    .fi-resource-create-record-page input:focus,
    .fi-resource-create-record-page textarea:focus,
    .fi-resource-create-record-page select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
    }

    /* File Upload Drop Zone */
    .fi-resource-create-record-page .fi-fo-file-upload {
        background-color: #090e1c !important;
        border: 1.5px dashed #222a3d !important;
        border-radius: 12px !important;
        transition: all 0.15s ease !important;
    }
    .fi-resource-create-record-page .fi-fo-file-upload:hover {
        border-color: #3b82f6 !important;
        background-color: rgba(30, 64, 175, 0.05) !important;
    }

    /* Primary Action Buttons */
    .fi-resource-create-record-page .fi-form-actions {
        background-color: #131b2e !important;
        border: 1.5px solid #222a3d !important;
        border-radius: 14px !important;
        padding: 14px 20px !important;
        margin-top: 20px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    }
    .fi-resource-create-record-page .fi-form-actions button[type="submit"],
    .fi-resource-create-record-page .fi-btn-color-primary {
        background-color: #1e40af !important;
        border: 1px solid #3b82f6 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
    }
    .fi-resource-create-record-page .fi-btn-color-primary:hover {
        background-color: #1d4ed8 !important;
    }
</style>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #dae2fd;">

    {{-- 1. Hero Context & Action Banner --}}
    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(30, 64, 175, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; position: relative; z-index: 1;">
            {{-- Left: Title & Subtitle --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4); flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin: 0; line-height: 1.2;">
                            Create New Task & Assignment
                        </h1>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 9px; border-radius: 9999px; border: 1px solid rgba(59, 130, 246, 0.3);">
                            ● Draft Milestone
                        </span>
                    </div>
                    <p style="font-size: 12.5px; color: #8e909f; margin: 4px 0 0 0;">
                        Define deliverable criteria, SLAs, attach project resources, and target specific cohorts, project teams, or individual interns.
                    </p>
                </div>
            </div>

            {{-- Right: Navigation Toolbar --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
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
                    Evaluation Hub
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Form Container --}}
    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="create"
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
