<x-filament-panels::page
    @class([
        'fi-resource-offer-letter-split-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <style>
        .ol-split-container {
            display: flex;
            flex-direction: row;
            gap: 0;
            width: 100%;
            min-height: 84vh;
            position: relative;
            background: transparent;
        }

        .ol-form-panel {
            flex: 1 1 52%;
            min-width: 360px;
            max-width: 75%;
            padding-right: 1.25rem;
            overflow-y: auto;
        }

        .ol-drag-divider {
            flex: 0 0 10px;
            cursor: col-resize;
            background-color: rgba(156, 163, 175, 0.25);
            border-radius: 9999px;
            margin: 0 0.25rem;
            transition: background-color 0.2s, box-shadow 0.2s;
            position: relative;
            user-select: none;
            touch-action: none;
        }

        .ol-drag-divider:hover,
        .ol-drag-divider.is-dragging {
            background-color: #f97316;
            box-shadow: 0 0 8px rgba(249, 115, 22, 0.4);
        }

        .ol-drag-divider::after {
            content: "⋮⋮";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #6b7280;
            font-size: 11px;
            letter-spacing: -2px;
            line-height: 1;
            pointer-events: none;
        }

        .ol-preview-panel {
            flex: 1 1 48%;
            min-width: 320px;
            display: flex;
            flex-direction: column;
            padding-left: 0.5rem;
        }

        .ol-preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .dark .ol-preview-toolbar {
            background: #1e293b;
            border-color: #334155;
        }

        .ol-preview-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .dark .ol-preview-title {
            color: #cbd5e1;
        }

        .ol-template-chip {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            transition: all 0.2s;
        }

        .dark .ol-template-chip {
            background: #0c4a6e;
            color: #7dd3fc;
            border-color: #0284c7;
        }

        .ol-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            background-color: #dcfce7;
            color: #166534;
        }

        .dark .ol-live-badge {
            background-color: #14532d;
            color: #86efac;
        }

        .ol-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #22c55e;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
        }

        .ol-refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fff;
            background-color: #f97316;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.15s, transform 0.1s;
        }

        .ol-refresh-btn:hover {
            background-color: #ea580c;
        }

        .ol-refresh-btn:active {
            transform: scale(0.97);
        }

        .ol-iframe-box {
            flex: 1;
            min-height: 70vh;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            position: relative;
        }

        .dark .ol-iframe-box {
            border-color: #334155;
            background: #0f172a;
        }

        .ol-preview-frame {
            width: 100%;
            height: 100%;
            min-height: 70vh;
            border: none;
            background: #fff;
        }

        /* Mobile View toggle */
        .ol-mobile-switcher {
            display: none;
            margin-bottom: 0.75rem;
        }

        @media (max-width: 900px) {
            .ol-mobile-switcher {
                display: flex;
                gap: 0.5rem;
            }
            .ol-split-container {
                flex-direction: column;
            }
            .ol-drag-divider {
                display: none;
            }
            .ol-form-panel {
                max-width: 100%;
                min-width: 100%;
                padding-right: 0;
            }
            .ol-preview-panel {
                max-width: 100%;
                min-width: 100%;
                padding-left: 0;
                margin-top: 1rem;
            }
            .ol-preview-panel.ol-hide-mobile {
                display: none;
            }
            .ol-form-panel.ol-hide-mobile {
                display: none;
            }
        }

        @keyframes spin-anim {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin-active {
            animation: spin-anim 0.8s linear infinite;
        }
    </style>

    <!-- Mobile Switcher -->
    <div class="ol-mobile-switcher">
        <button
            type="button"
            id="ol-switch-form"
            onclick="switchMobileTab('form')"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-orange-500 text-white shadow-sm"
        >
            📝 Form
        </button>
        <button
            type="button"
            id="ol-switch-preview"
            onclick="switchMobileTab('preview')"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300"
        >
            📄 Live Preview
        </button>
    </div>

    <div class="ol-split-container" id="ol-split-container">
        <!-- LEFT: Filament Form -->
        <div class="ol-form-panel" id="ol-form-panel">
            <x-filament-panels::form
                id="form"
                :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
                :wire:submit="method_exists($this, 'create') ? 'create' : 'save'"
            >
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>
        </div>

        <!-- DRAG RESIZE DIVIDER -->
        <div class="ol-drag-divider" id="ol-drag-divider" title="Drag to adjust Form and Preview space"></div>

        <!-- RIGHT: Live Offer Letter Preview -->
        <div class="ol-preview-panel" id="ol-preview-panel">
            <div class="ol-preview-toolbar">
                <div class="ol-preview-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Offer Preview</span>
                    <span class="ol-live-badge">
                        <span class="ol-live-dot"></span> Real-Time
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span id="ol-template-chip" class="ol-template-chip">General</span>
                    <button type="button" class="ol-refresh-btn" onclick="triggerOfferPreviewRefresh(true)">
                        <svg id="ol-refresh-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <div class="ol-iframe-box">
                <iframe
                    id="ol-preview-frame"
                    class="ol-preview-frame"
                    src="about:blank"
                ></iframe>
            </div>
        </div>
    </div>

    <script>
    let olActiveAbortController = null;
    let olDebounceTimer = null;
    let olLastRenderedSignature = '';

    document.addEventListener('DOMContentLoaded', function () {
        initOfferLetterSplitPane();
    });

    document.addEventListener('livewire:navigated', function () {
        initOfferLetterSplitPane();
    });

    // Listen to Livewire v3 commit hooks
    if (window.Livewire) {
        setupLivewirePreviewHooks();
    } else {
        document.addEventListener('livewire:initialized', setupLivewirePreviewHooks);
    }

    function setupLivewirePreviewHooks() {
        if (!window.Livewire || window._olLivewireHooked) return;
        window._olLivewireHooked = true;

        window.Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                // Whenever Livewire finishes an update (e.g. template changed or intern selected), re-render preview
                setTimeout(() => triggerOfferPreviewRefresh(true), 50);
            });
        });
    }

    function initOfferLetterSplitPane() {
        const container = document.getElementById('ol-split-container');
        const formPanel = document.getElementById('ol-form-panel');
        const divider = document.getElementById('ol-drag-divider');
        const previewPanel = document.getElementById('ol-preview-panel');

        if (!container || !divider || !formPanel) return;

        // ── Drag divider logic ──────────────────────────────────────────────
        let isDragging = false;

        divider.addEventListener('mousedown', function (e) {
            isDragging = true;
            divider.classList.add('is-dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', function (e) {
            if (!isDragging) return;
            const containerRect = container.getBoundingClientRect();
            const totalWidth = containerRect.width;
            const currentX = e.clientX - containerRect.left;

            const minWidth = totalWidth * 0.30;
            const maxWidth = totalWidth * 0.75;
            const clampedWidth = Math.min(Math.max(currentX, minWidth), maxWidth);

            formPanel.style.flex = '0 0 ' + clampedWidth + 'px';
            previewPanel.style.flex = '1 1 auto';
        });

        document.addEventListener('mouseup', function () {
            if (isDragging) {
                isDragging = false;
                divider.classList.remove('is-dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            }
        });

        divider.addEventListener('touchstart', function () { isDragging = true; });
        document.addEventListener('touchmove', function (e) {
            if (!isDragging) return;
            const touch = e.touches[0];
            const containerRect = container.getBoundingClientRect();
            const totalWidth = containerRect.width;
            const currentX = touch.clientX - containerRect.left;

            const minWidth = totalWidth * 0.30;
            const maxWidth = totalWidth * 0.75;
            const clampedWidth = Math.min(Math.max(currentX, minWidth), maxWidth);

            formPanel.style.flex = '0 0 ' + clampedWidth + 'px';
            previewPanel.style.flex = '1 1 auto';
        });
        document.addEventListener('touchend', function () { isDragging = false; });

        // ── REAL-TIME EVENT LISTENERS ON FORM ────────────────────────────────
        // Real-time keystrokes: fast 120ms debounce for instant typing feel
        formPanel.addEventListener('input', function () {
            clearTimeout(olDebounceTimer);
            olDebounceTimer = setTimeout(() => triggerOfferPreviewRefresh(false), 120);
        });

        // Instant update on select/date change (0ms delay)
        formPanel.addEventListener('change', function () {
            clearTimeout(olDebounceTimer);
            triggerOfferPreviewRefresh(true);
        });

        // RichEditor (Trix) change listener for real-time description preview
        formPanel.addEventListener('trix-change', function () {
            clearTimeout(olDebounceTimer);
            olDebounceTimer = setTimeout(() => triggerOfferPreviewRefresh(false), 150);
        });

        // Keyup / paste fallback for immediate response
        formPanel.addEventListener('paste', function () {
            setTimeout(() => triggerOfferPreviewRefresh(true), 50);
        });

        // MutationObserver to catch Livewire DOM updates or auto-fills
        const observer = new MutationObserver(function (mutations) {
            let shouldRefresh = false;
            for (let m of mutations) {
                if (m.type === 'childList' || (m.type === 'attributes' && (m.attributeName === 'value' || m.attributeName === 'selected'))) {
                    shouldRefresh = true;
                    break;
                }
            }
            if (shouldRefresh) {
                clearTimeout(olDebounceTimer);
                olDebounceTimer = setTimeout(() => triggerOfferPreviewRefresh(false), 150);
            }
        });
        observer.observe(formPanel, { childList: true, subtree: true, attributes: true });

        // Initial preview load
        setTimeout(() => triggerOfferPreviewRefresh(true), 250);
    }

    function switchMobileTab(tab) {
        const formPanel = document.getElementById('ol-form-panel');
        const previewPanel = document.getElementById('ol-preview-panel');
        const btnForm = document.getElementById('ol-switch-form');
        const btnPreview = document.getElementById('ol-switch-preview');

        if (tab === 'form') {
            formPanel.classList.remove('ol-hide-mobile');
            previewPanel.classList.add('ol-hide-mobile');
            btnForm.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-orange-500 text-white shadow-sm';
            btnPreview.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300';
        } else {
            formPanel.classList.add('ol-hide-mobile');
            previewPanel.classList.remove('ol-hide-mobile');
            btnPreview.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-orange-500 text-white shadow-sm';
            btnForm.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300';
            triggerOfferPreviewRefresh(true);
        }
    }

    function readFieldValue(name) {
        // Try id: data.{name}
        let el = document.getElementById('data.' + name);
        if (el && el.value !== undefined && el.value !== null && el.value !== '') {
            return el.value;
        }

        // Try wire:model
        el = document.querySelector(`[wire\\:model="data.${name}"], [wire\\:model\\.live="data.${name}"], [wire\\:model\\.defer="data.${name}"]`);
        if (el && el.value !== undefined && el.value !== null && el.value !== '') {
            return el.value;
        }

        // Try name
        el = document.querySelector(`[name="data[${name}]"], [name="${name}"]`);
        if (el && el.value !== undefined && el.value !== null && el.value !== '') {
            return el.value;
        }

        // Try Livewire component state
        try {
            const formEl = document.getElementById('form');
            const lwEl = formEl ? formEl.closest('[wire\\:id]') : null;
            if (lwEl && window.Livewire) {
                const lw = window.Livewire.find(lwEl.getAttribute('wire:id'));
                if (lw && lw.data && lw.data[name]) {
                    return lw.data[name];
                }
            }
        } catch (e) {}

        return '';
    }

    function getOfferFormData() {
        const fields = [
            'template',
            'name',
            'college',
            'university',
            'degree',
            'email',
            'phone',
            'joining_date',
            'completion_date',
            'offer_issue_date',
            'internship_role',
            'internship_position',
            'working_hours',
            'description',
        ];

        const data = {};
        fields.forEach(f => {
            data[f] = readFieldValue(f);
        });

        // Special handling for Trix / RichEditor description if present
        const trix = document.querySelector('trix-editor');
        if (trix) {
            const inputId = trix.getAttribute('input');
            const hiddenInput = inputId ? document.getElementById(inputId) : null;
            if (hiddenInput && hiddenInput.value) {
                data['description'] = hiddenInput.value;
            } else if (trix.value) {
                data['description'] = trix.value;
            } else if (trix.innerHTML) {
                data['description'] = trix.innerHTML;
            }
        }

        return data;
    }

    async function triggerOfferPreviewRefresh(force = false) {
        const frame = document.getElementById('ol-preview-frame');
        const templateChip = document.getElementById('ol-template-chip');
        const icon = document.getElementById('ol-refresh-icon');

        if (!frame) return;

        const data = getOfferFormData();
        const template = data.template || 'general';

        // Check if data actually changed to avoid unnecessary re-renders
        const currentSignature = JSON.stringify(data);
        if (!force && currentSignature === olLastRenderedSignature) {
            return;
        }
        olLastRenderedSignature = currentSignature;

        const templateNames = {
            '3_month_offer_letter': '3 Month',
            '4_month_offer_letter': '4 Month',
            '6_month_offer_letter': '6 Month',
            'one_month': '1 Month',
            'general': 'General'
        };

        if (templateChip) {
            templateChip.textContent = templateNames[template] || template;
        }

        // Abort previous in-flight request so only latest keystroke renders
        if (olActiveAbortController) {
            olActiveAbortController.abort();
        }
        olActiveAbortController = new AbortController();

        if (icon) icon.classList.add('spin-active');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value
                || '';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            Object.entries(data).forEach(([k, v]) => {
                if (v !== undefined && v !== null) formData.append(k, v);
            });

            const res = await fetch('/admin/offer-letters/preview', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                signal: olActiveAbortController.signal,
            });

            if (res.ok) {
                const html = await res.text();
                // Use srcdoc for instant, smooth, flicker-free rendering
                frame.srcdoc = html;
            }
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error('Offer letter preview refresh failed', e);
            }
        } finally {
            if (icon) icon.classList.remove('spin-active');
        }
    }
    </script>
</x-filament-panels::page>
