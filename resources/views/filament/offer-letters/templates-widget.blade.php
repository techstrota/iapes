<x-filament-widgets::widget>
    @php
        $templates = $this->templates;
    @endphp

    <div style="background-color: #131b2e; border: 1.5px solid #222a3d; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25); overflow: hidden;">
        {{-- Widget Header --}}
        <div style="background-color: #0b1326; border-bottom: 1.5px solid #1c2638; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="font-size: 15px; font-weight: 800; color: #ffffff; display: inline-flex; align-items: center; gap: 8px;">
                    <span>🔖</span>
                    <span>Offer Letter Templates</span>
                </span>
                <span style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 11.5px; font-weight: 700; padding: 2px 9px; border-radius: 9999px;">
                    {{ $templates->count() }} Saved
                </span>
                <span style="font-size: 12px; color: #8e909f;">
                    Create once, generate offer letters for multiple candidates at once, or edit to auto-sync linked drafts.
                </span>
            </div>

            <button
                type="button"
                wire:click="mountAction('createTemplate')"
                style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: 1px solid #3b82f6; color: #ffffff; font-size: 12.5px; font-weight: 700; padding: 7px 14px; border-radius: 9px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35);">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
                </svg>
                New Template
            </button>
        </div>

        {{-- Templates Grid --}}
        <div style="padding: 16px 20px;">
            @if ($templates->isEmpty())
                <div style="background-color: #0b1326; border: 1.5px dashed #222a3d; border-radius: 12px; padding: 22px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="font-size: 14px; font-weight: 700; color: #ffffff;">No Offer Letter Templates Created Yet</div>
                    <div style="font-size: 12.5px; color: #8e909f; max-width: 520px;">
                        Create a reusable Offer Letter Template with your role, position, joining/completion dates, working hours, and custom body to generate offer letters for shortlisted candidates in bulk.
                    </div>
                    <button
                        type="button"
                        wire:click="mountAction('createTemplate')"
                        style="margin-top: 4px; background-color: #1e40af; border: 1px solid #3b82f6; color: #ffffff; font-size: 12.5px; font-weight: 700; padding: 7px 16px; border-radius: 8px; cursor: pointer;">
                        + Create First Template
                    </button>
                </div>
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 14px;">
                    @foreach ($templates as $t)
                        @php
                            $join = $t->joining_date ? $t->joining_date->format('d M Y') : 'Pending';
                            $comp = $t->completion_date ? $t->completion_date->format('d M Y') : 'Pending';
                            $issue = $t->offer_issue_date ? $t->offer_issue_date->format('d M Y') : 'Not Set';
                            $hours = $t->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday';
                            $role = $t->internship_role ?: 'Role Not Set';
                            $pos = $t->internship_position ?: '';
                            $roleLine = $pos ? "{$role} • {$pos}" : $role;
                        @endphp

                        <div style="background-color: #0b1326; border: 1.5px solid #1c2638; border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);">
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                {{-- Card Header: Template Name, Role, and Badges --}}
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    {{-- Template Name (Full Width) --}}
                                    <div style="font-size: 15px; font-weight: 800; color: #ffffff; line-height: 1.35; word-break: break-word; display: flex; align-items: flex-start; gap: 6px;" title="{{ $t->name }}">
                                        <span style="color: #60a5fa; flex-shrink: 0; font-size: 15px;">🔖</span>
                                        <span>{{ $t->name }}</span>
                                    </div>

                                    {{-- Role & Position (Full Width) --}}
                                    <div style="font-size: 13px; font-weight: 600; color: #93c5fd; line-height: 1.35; word-break: break-word; padding-left: 2px;" title="{{ $roleLine }}">
                                        {{ $roleLine }}
                                    </div>

                                    {{-- Badges Row --}}
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 3px;">
                                        <span style="background-color: rgba(37, 99, 235, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); font-size: 10.5px; font-weight: 700; padding: 2.5px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            ⏱️ {{ $t->duration_label }}
                                        </span>
                                        <span style="background-color: #0c1c38; color: #60a5fa; border: 1px solid #1d4ed8; font-size: 10.5px; font-weight: 700; padding: 2.5px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            📄 {{ $t->layout_label }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Schedule & Working Hours Box --}}
                                <div style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid #1c2638; border-radius: 8px; padding: 8px 11px; display: flex; flex-direction: column; gap: 5px; font-size: 11.5px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                        <span style="color: #cbd5e1;">
                                            <strong style="color: #8e909f;">Tenure:</strong>
                                            <span style="color: #ffffff; font-weight: 700;">{{ $join }}</span>
                                            <span style="color: #60a5fa;">→</span>
                                            <span style="color: #ffffff; font-weight: 700;">{{ $comp }}</span>
                                        </span>
                                        <span style="color: #94a3b8; font-size: 11px;">
                                            <strong>Issue:</strong> <span style="color: #e2e8f0;">{{ $issue }}</span>
                                        </span>
                                    </div>
                                    <div style="color: #94a3b8; word-break: break-word; line-height: 1.35;">
                                        <strong style="color: #8e909f;">Hours:</strong> <span style="color: #cbd5e1;">{{ $hours }}</span>
                                    </div>
                                </div>

                                {{-- Linked Letters Counts --}}
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11px;">
                                    <span style="background-color: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.1); padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                                        📊 Total: <strong>{{ $t->total_count }}</strong>
                                    </span>
                                    <span style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                                        🟡 Drafts: <strong>{{ $t->draft_count }}</strong>
                                    </span>
                                    <span style="background-color: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                                        ✅ Accepted: <strong>{{ $t->accepted_count }}</strong>
                                    </span>
                                </div>
                            </div>

                            {{-- Card Actions Footer --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.06); flex-wrap: wrap;">
                                <button
                                    type="button"
                                    wire:click="mountAction('applyTemplate', { template: {{ $t->id }} })"
                                    style="flex: 1 1 auto; background-color: #1e40af; border: 1px solid #3b82f6; color: #ffffff; font-size: 11.5px; font-weight: 700; padding: 5px 10px; border-radius: 7px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px;">
                                    <span>⚡ Generate for Candidates</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="mountAction('editTemplate', { template: {{ $t->id }} })"
                                    style="background-color: #131b2e; border: 1px solid #233149; color: #60a5fa; font-size: 11.5px; font-weight: 700; padding: 5px 10px; border-radius: 7px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                                    title="Edit Template & Auto-Sync Linked Drafts">
                                    <span>✏️ Edit</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="mountAction('deleteTemplate', { template: {{ $t->id }} })"
                                    style="background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; font-size: 11.5px; font-weight: 700; padding: 5px 9px; border-radius: 7px; cursor: pointer; display: inline-flex; align-items: center;"
                                    title="Delete Template">
                                    <span>🗑️</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
