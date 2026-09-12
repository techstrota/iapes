<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandname('IAPES_Admin')
            ->colors([
            'primary' => '#f7a93b', // The Orange/Gold from your logo
            ])
            ->brandLogo(asset('images/TsLogo.png'))
            ->brandLogoHeight('3rem')
            // ✅ COLLAPSIBLE SIDEBAR
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4.5rem')
             // ✅ FULL WIDTH CONTENT
            ->maxContentWidth('full')

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\ApplicationStats::class,
                
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => '
                    <style>
                        /* Align selection checkbox to top-left of candidate card */
                        .fi-ta-content-grid .fi-ta-record > div.flex {
                            align-items: flex-start !important;
                        }
                        .fi-ta-content-grid .fi-ta-record-checkbox {
                            margin-top: 1.15rem !important;
                            margin-inline-start: 0.75rem !important;
                        }
                        /* Place edit button in top-right corner of card */
                        .fi-ta-card-edit-btn {
                            position: absolute !important;
                            top: 0.75rem !important;
                            right: 0.75rem !important;
                            z-index: 2 !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            width: 2rem !important;
                            height: 2rem !important;
                            border-radius: 0.5rem !important;
                            background-color: rgba(243, 244, 246, 0.8) !important;
                            border: 1px solid rgba(209, 213, 219, 0.8) !important;
                            transition: all 0.15s ease !important;
                        }
                        :is(.dark) .fi-ta-card-edit-btn {
                            background-color: rgba(31, 41, 55, 0.8) !important;
                            border-color: rgba(75, 85, 99, 0.7) !important;
                        }
                        .fi-ta-card-edit-btn:hover {
                            background-color: rgba(229, 231, 235, 1) !important;
                            transform: scale(1.05) !important;
                        }
                        :is(.dark) .fi-ta-card-edit-btn:hover {
                            background-color: rgba(55, 65, 81, 1) !important;
                        }
                        /* Place resume button in top-right corner next to edit icon */
                        .fi-ta-card-resume-btn {
                            position: absolute !important;
                            top: 0.85rem !important;
                            right: 2.75rem !important;
                            z-index: 2 !important;
                        }
                        .fi-ta-card-resume-btn:disabled,
                        .fi-ta-card-resume-btn[disabled] {
                            opacity: 0.35 !important;
                            cursor: not-allowed !important;
                        }
                        /* Ensure dropdown panels (like table filters popover) always sit cleanly above cards and actions */
                        .fi-dropdown-panel {
                            z-index: 40 !important;
                        }
                        /* Prevent unwanted table horizontal scroll */
                        .fi-ta-content-grid {
                            overflow-x: hidden !important;
                            width: 100% !important;
                        }
                        .fi-ta-content {
                            overflow-x: hidden !important;
                        }
                        /* Responsive card record container & flex min-width reset */
                        .fi-ta-content-grid .fi-ta-record {
                            position: relative !important;
                            isolation: isolate !important;
                            z-index: 1 !important;
                            overflow: hidden !important;
                            min-width: 0 !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        }
                        .fi-ta-content-grid .fi-ta-record > div {
                            min-width: 0 !important;
                            width: 100% !important;
                        }
                        .fi-ta-content-grid .fi-ta-stack {
                            min-width: 0 !important;
                            max-width: 100% !important;
                            width: 100% !important;
                            align-items: stretch !important;
                            box-sizing: border-box !important;
                        }
                        /* Row 1 Header: keeps code & badge on left, reserves right side for icons, wraps cleanly on small screens */
                        .fi-ta-card-header-split {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: flex-start !important;
                            flex-wrap: wrap !important;
                            gap: 0.5rem !important;
                            padding-right: 5.25rem !important; /* Space for top-right edit & resume icons */
                            min-height: 2rem !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        }
                        /* Name responsive word wrapping */
                        .fi-ta-card-name {
                            word-break: break-word !important;
                            overflow-wrap: break-word !important;
                            line-height: 1.35 !important;
                            max-width: 100% !important;
                        }
                        /* Email & College single-line truncate so long text never stretches card */
                        .fi-ta-card-truncate,
                        .fi-ta-card-truncate .fi-ta-text,
                        .fi-ta-card-truncate .fi-ta-text-item {
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            white-space: nowrap !important;
                            max-width: 100% !important;
                        }
                        /* Row 5 Meta: CGPA and Date split evenly */
                        .fi-ta-card-meta-split {
                            display: flex !important;
                            justify-content: space-between !important;
                            align-items: center !important;
                            flex-wrap: wrap !important;
                            gap: 0.35rem !important;
                            width: 100% !important;
                        }
                        /* Responsive Action Buttons container at bottom of card */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            gap: 0.45rem !important;
                            width: 100% !important;
                            margin-top: 0.75rem !important;
                            box-sizing: border-box !important;
                        }
                        /* Each action button (excluding the absolute top-right icons) */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action:not(.fi-ta-card-edit-btn):not(.fi-ta-card-resume-btn) {
                            flex: 1 1 calc(33.333% - 0.45rem) !important;
                            min-width: 90px !important;
                            box-sizing: border-box !important;
                        }
                        /* Fill button width with centered text and zero cut-off */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action:not(.fi-ta-card-edit-btn):not(.fi-ta-card-resume-btn) .fi-btn {
                            width: 100% !important;
                            justify-content: center !important;
                            white-space: nowrap !important;
                            font-size: 0.8rem !important;
                            padding: 0.45rem 0.5rem !important;
                            box-sizing: border-box !important;
                        }
                        @media (max-width: 420px) {
                            .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action:not(.fi-ta-card-edit-btn):not(.fi-ta-card-resume-btn) {
                                flex: 1 1 100% !important;
                            }
                        }
                        /* Hide empty column wrappers in card stack */
                        .fi-ta-content-grid .fi-ta-stack > .fi-ta-col-wrapper:empty {
                            display: none !important;
                        }
                        /* Full width and styling for Candidate Card Interview Marks */
                        .fi-ta-marks-col,
                        .fi-ta-marks-col > div,
                        .fi-ta-marks-col .fi-ta-text,
                        .fi-ta-marks-col .fi-ta-text-item,
                        .fi-ta-marks-card {
                            width: 100% !important;
                            max-width: 100% !important;
                            display: block !important;
                            box-sizing: border-box !important;
                        }
                        .fi-ta-marks-card {
                            border-radius: 0.5rem;
                            padding: 0.65rem 0.75rem;
                            margin-top: 0.4rem;
                            border: 1px solid rgba(156, 163, 175, 0.25);
                            background-color: rgba(243, 244, 246, 0.8);
                        }
                        :is(.dark) .fi-ta-marks-card {
                            border-color: rgba(255, 255, 255, 0.1) !important;
                            background-color: rgba(255, 255, 255, 0.04) !important;
                        }
                        .fi-ta-marks-header {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                            flex-wrap: wrap !important;
                            gap: 0.35rem !important;
                            padding-bottom: 0.4rem !important;
                            margin-bottom: 0.45rem !important;
                            border-bottom: 1px solid rgba(156, 163, 175, 0.2);
                        }
                        :is(.dark) .fi-ta-marks-header {
                            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-ta-marks-title {
                            font-size: 0.72rem !important;
                            font-weight: 700 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                            color: #4b5563;
                            display: flex !important;
                            align-items: center !important;
                        }
                        :is(.dark) .fi-ta-marks-title {
                            color: #d1d5db !important;
                        }
                        .fi-ta-marks-grid {
                            display: grid !important;
                            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                            gap: 0.5rem !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        }
                        .fi-ta-marks-box {
                            border-radius: 0.375rem;
                            padding: 0.45rem 0.6rem;
                            background-color: #ffffff;
                            border: 1px solid rgba(229, 231, 235, 1);
                            display: flex;
                            flex-direction: column;
                            box-sizing: border-box;
                        }
                        :is(.dark) .fi-ta-marks-box {
                            background-color: rgba(0, 0, 0, 0.25) !important;
                            border-color: rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-ta-marks-box-label {
                            font-size: 0.68rem !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                            font-weight: 600 !important;
                            color: #6b7280;
                        }
                        :is(.dark) .fi-ta-marks-box-label {
                            color: #9ca3af !important;
                        }
                        .fi-ta-marks-box-val {
                            font-size: 0.95rem !important;
                            font-weight: 700 !important;
                            color: #111827;
                        }
                        :is(.dark) .fi-ta-marks-box-val {
                            color: #f3f4f6 !important;
                        }
                        .fi-ta-marks-pending {
                            font-size: 0.75rem !important;
                            font-weight: 600 !important;
                            color: #d97706;
                        }
                        :is(.dark) .fi-ta-marks-pending {
                            color: #fbbf24 !important;
                        }
                        .fi-ta-badge-evaluated {
                            display: inline-flex;
                            align-items: center;
                            border-radius: 9999px;
                            padding: 0.15rem 0.5rem;
                            font-size: 0.72rem;
                            font-weight: 700;
                            background-color: #dcfce7;
                            color: #166534;
                            border: 1px solid #bbf7d0;
                        }
                        :is(.dark) .fi-ta-badge-evaluated {
                            background-color: rgba(22, 101, 52, 0.4) !important;
                            color: #86efac !important;
                            border-color: rgba(74, 222, 128, 0.3) !important;
                        }
                        .fi-ta-badge-partial {
                            display: inline-flex;
                            align-items: center;
                            border-radius: 9999px;
                            padding: 0.15rem 0.5rem;
                            font-size: 0.72rem;
                            font-weight: 700;
                            background-color: #fef3c7;
                            color: #92400e;
                            border: 1px solid #fde68a;
                        }
                        :is(.dark) .fi-ta-badge-partial {
                            background-color: rgba(180, 83, 9, 0.35) !important;
                            color: #fde047 !important;
                            border-color: rgba(250, 204, 21, 0.3) !important;
                        }
                        .fi-ta-badge-notgraded {
                            display: inline-flex;
                            align-items: center;
                            border-radius: 9999px;
                            padding: 0.15rem 0.5rem;
                            font-size: 0.7rem;
                            font-weight: 500;
                            background-color: #f3f4f6;
                            color: #6b7280;
                            border: 1px solid #e5e7eb;
                        }
                        :is(.dark) .fi-ta-badge-notgraded {
                            background-color: rgba(255, 255, 255, 0.08) !important;
                            color: #9ca3af !important;
                            border-color: rgba(255, 255, 255, 0.12) !important;
                        }
                        .fi-ta-badge-absent {
                            display: inline-flex;
                            align-items: center;
                            border-radius: 9999px;
                            padding: 0.15rem 0.5rem;
                            font-size: 0.72rem;
                            font-weight: 700;
                            background-color: #fee2e2;
                            color: #991b1b;
                            border: 1px solid #fecaca;
                        }
                        :is(.dark) .fi-ta-badge-absent {
                            background-color: rgba(153, 27, 27, 0.4) !important;
                            color: #fca5a5 !important;
                            border-color: rgba(248, 113, 113, 0.3) !important;
                        }
                        /* Candidate Card Batch Badge styling */
                        .fi-ta-card-batch-badge {
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 0.35rem !important;
                            padding: 0.35rem 0.65rem !important;
                            border-radius: 0.5rem !important;
                            font-size: 0.78rem !important;
                            font-weight: 600 !important;
                            background-color: #f0f9ff !important;
                            color: #0369a1 !important;
                            border: 1px solid #bae6fd !important;
                            max-width: 100% !important;
                            box-sizing: border-box !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            white-space: nowrap !important;
                        }
                        :is(.dark) .fi-ta-card-batch-badge {
                            background-color: rgba(14, 165, 233, 0.12) !important;
                            color: #7dd3fc !important;
                            border-color: rgba(56, 189, 248, 0.25) !important;
                        }
                        .fi-ta-card-batch-badge svg {
                            width: 0.95rem !important;
                            height: 0.95rem !important;
                            flex-shrink: 0 !important;
                            color: #0284c7 !important;
                        }
                        :is(.dark) .fi-ta-card-batch-badge svg {
                            color: #38bdf8 !important;
                        }

                        /* ────────────────────────── Interview Batch Cards ────────────────────────── */
                        .fi-batch-card-header {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                            gap: 0.5rem !important;
                            width: 100% !important;
                            padding-right: 3rem !important; /* Space for the top-right edit button */
                            box-sizing: border-box !important;
                            min-height: 2rem !important;
                        }
                        .fi-batch-card-badges {
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.35rem !important;
                            flex-wrap: wrap !important;
                            justify-content: flex-end !important;
                        }
                        .fi-batch-schedule-card {
                            display: flex !important;
                            flex-direction: column !important;
                            gap: 0.5rem !important;
                            padding: 0.75rem 0.85rem !important;
                            background-color: #f8fafc !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 0.625rem !important;
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                            box-sizing: border-box !important;
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-batch-schedule-card {
                            background-color: rgba(255, 255, 255, 0.03) !important;
                            border-color: rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-batch-schedule-item {
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.55rem !important;
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                            box-sizing: border-box !important;
                        }
                        .fi-batch-item-icon {
                            width: 1.1rem !important;
                            height: 1.1rem !important;
                            flex-shrink: 0 !important;
                        }
                        .fi-batch-item-content {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                            flex: 1 1 0% !important;
                            min-width: 0 !important;
                            max-width: 100% !important;
                            gap: 0.5rem !important;
                            box-sizing: border-box !important;
                        }
                        .fi-batch-item-title {
                            font-size: 0.82rem !important;
                            color: #1e293b !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.35rem !important;
                            min-width: 0 !important;
                            flex-shrink: 1 !important;
                        }
                        :is(.dark) .fi-batch-item-title {
                            color: #f1f5f9 !important;
                        }
                        .fi-batch-item-weekday {
                            font-size: 0.75rem !important;
                            color: #64748b !important;
                            font-weight: 500 !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-item-weekday {
                            color: #94a3b8 !important;
                        }
                        .fi-batch-time-group {
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.35rem !important;
                            min-width: 0 !important;
                            flex-wrap: nowrap !important;
                            flex-shrink: 1 !important;
                        }
                        .fi-batch-time-text {
                            font-size: 0.8rem !important;
                            color: #1e293b !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-time-text {
                            color: #f1f5f9 !important;
                        }
                        .fi-batch-duration-badge {
                            font-size: 0.72rem !important;
                            font-weight: 600 !important;
                            color: #64748b !important;
                            background-color: rgba(0, 0, 0, 0.05) !important;
                            padding: 0.1rem 0.45rem !important;
                            border-radius: 0.25rem !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-duration-badge {
                            color: #94a3b8 !important;
                            background-color: rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-batch-location-row {
                            align-items: flex-start !important;
                        }
                        .fi-batch-location-row .fi-batch-item-icon {
                            margin-top: 0.15rem !important;
                        }
                        .fi-batch-location-wrapper {
                            flex: 1 1 0% !important;
                            min-width: 0 !important;
                            max-width: 100% !important;
                            overflow: hidden !important;
                        }
                        .fi-batch-location-text {
                            display: -webkit-box !important;
                            -webkit-line-clamp: 2 !important;
                            -webkit-box-orient: vertical !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            word-break: break-word !important;
                            font-size: 0.8rem !important;
                            line-height: 1.35 !important;
                            color: #334155 !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                            width: 100% !important;
                        }
                        :is(.dark) .fi-batch-location-text {
                            color: #cbd5e1 !important;
                        }
                        .fi-batch-type-online {
                            font-size: 0.65rem !important;
                            font-weight: 700 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.04em !important;
                            padding: 0.15rem 0.45rem !important;
                            border-radius: 9999px !important;
                            background-color: #ede9fe !important;
                            color: #5b21b6 !important;
                            border: 1px solid #ddd6fe !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-type-online {
                            background-color: rgba(109, 40, 217, 0.25) !important;
                            color: #c4b5fd !important;
                            border-color: rgba(109, 40, 217, 0.4) !important;
                        }
                        .fi-batch-type-onsite {
                            font-size: 0.65rem !important;
                            font-weight: 700 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.04em !important;
                            padding: 0.15rem 0.45rem !important;
                            border-radius: 9999px !important;
                            background-color: #ecfdf5 !important;
                            color: #065f46 !important;
                            border: 1px solid #a7f3d0 !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-type-onsite {
                            background-color: rgba(5, 150, 105, 0.2) !important;
                            color: #6ee7b7 !important;
                            border-color: rgba(5, 150, 105, 0.3) !important;
                        }

                        /* Relative date badges */
                        .fi-batch-rel-today {
                            font-size: 0.65rem !important;
                            font-weight: 700 !important;
                            padding: 0.1rem 0.35rem !important;
                            border-radius: 9999px !important;
                            background-color: #fef3c7 !important;
                            color: #92400e !important;
                            border: 1px solid #fde68a !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-rel-today {
                            background-color: rgba(245, 158, 11, 0.2) !important;
                            color: #fcd34d !important;
                            border-color: rgba(245, 158, 11, 0.3) !important;
                        }
                        .fi-batch-rel-tomorrow {
                            font-size: 0.65rem !important;
                            font-weight: 700 !important;
                            padding: 0.1rem 0.35rem !important;
                            border-radius: 9999px !important;
                            background-color: #e0f2fe !important;
                            color: #0369a1 !important;
                            border: 1px solid #bae6fd !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-rel-tomorrow {
                            background-color: rgba(14, 165, 233, 0.2) !important;
                            color: #7dd3fc !important;
                            border-color: rgba(14, 165, 233, 0.3) !important;
                        }
                        .fi-batch-rel-future {
                            font-size: 0.65rem !important;
                            font-weight: 600 !important;
                            padding: 0.1rem 0.35rem !important;
                            border-radius: 9999px !important;
                            background-color: #f1f5f9 !important;
                            color: #475569 !important;
                            border: 1px solid #e2e8f0 !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-rel-future {
                            background-color: rgba(255, 255, 255, 0.06) !important;
                            color: #94a3b8 !important;
                            border-color: rgba(255, 255, 255, 0.1) !important;
                        }
                        .fi-batch-rel-past {
                            font-size: 0.65rem !important;
                            font-weight: 600 !important;
                            padding: 0.1rem 0.35rem !important;
                            border-radius: 9999px !important;
                            background-color: #f1f5f9 !important;
                            color: #94a3b8 !important;
                            border: 1px solid #e2e8f0 !important;
                            white-space: nowrap !important;
                            flex-shrink: 0 !important;
                        }
                        :is(.dark) .fi-batch-rel-past {
                            background-color: rgba(255, 255, 255, 0.04) !important;
                            color: #64748b !important;
                            border-color: rgba(255, 255, 255, 0.06) !important;
                        }

                        /* Capacity meter */
                        .fi-batch-capacity-box {
                            display: flex !important;
                            flex-direction: column !important;
                            gap: 0.4rem !important;
                            padding: 0.65rem 0.85rem !important;
                            background-color: #ffffff !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 0.625rem !important;
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                            align-self: stretch !important;
                            box-sizing: border-box !important;
                        }
                        :is(.dark) .fi-batch-capacity-box {
                            background-color: rgba(255, 255, 255, 0.02) !important;
                            border-color: rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-batch-capacity-header {
                            display: flex !important;
                            justify-content: space-between !important;
                            align-items: center !important;
                            width: 100% !important;
                            gap: 0.5rem !important;
                        }
                        .fi-batch-capacity-left {
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.4rem !important;
                            font-size: 0.78rem !important;
                            color: #475569 !important;
                        }
                        :is(.dark) .fi-batch-capacity-left {
                            color: #94a3b8 !important;
                        }
                        .fi-batch-capacity-title {
                            font-weight: 500 !important;
                        }
                        .fi-batch-capacity-nums {
                            color: #0f172a !important;
                        }
                        :is(.dark) .fi-batch-capacity-nums {
                            color: #f8fafc !important;
                        }
                        .fi-batch-cap-open {
                            font-size: 0.75rem !important;
                            font-weight: 700 !important;
                            color: #059669 !important;
                            white-space: nowrap !important;
                        }
                        :is(.dark) .fi-batch-cap-open {
                            color: #34d399 !important;
                        }
                        .fi-batch-cap-full {
                            font-size: 0.75rem !important;
                            font-weight: 700 !important;
                            color: #dc2626 !important;
                            white-space: nowrap !important;
                        }
                        :is(.dark) .fi-batch-cap-full {
                            color: #f87171 !important;
                        }
                        .fi-batch-progress-track {
                            width: 100% !important;
                            height: 0.5rem !important;
                            background-color: #e2e8f0 !important;
                            border-radius: 9999px !important;
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-batch-progress-track {
                            background-color: rgba(255, 255, 255, 0.1) !important;
                        }
                        .fi-batch-progress-bar {
                            height: 100% !important;
                            border-radius: 9999px !important;
                            transition: width 0.3s ease !important;
                        }

                        /* Ensure Card Stack containers span full width */
                        .fi-ta-content-grid .fi-ta-stack > .fi-ta-col-wrapper {
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                        }
                        .fi-ta-content-grid .fi-ta-stack .fi-ta-text {
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                        }
                        .fi-ta-content-grid .fi-ta-stack .fi-ta-text-item {
                            width: 100% !important;
                            max-width: 100% !important;
                            min-width: 0 !important;
                            display: block !important;
                        }

                        /* Card action buttons - icon buttons stay compact */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-batch-delete-btn,
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action:has(.fi-batch-delete-btn),
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action:has(.fi-icon-btn) {
                            flex: 0 0 auto !important;
                            min-width: auto !important;
                            width: auto !important;
                        }
                        .fi-batch-delete-btn .fi-icon-btn,
                        .fi-batch-delete-btn button {
                            padding: 0.45rem !important;
                        }

                        /* Candidate breakdown pills */
                        .fi-batch-pills-row {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 0.35rem;
                        }
                        .fi-batch-empty-pills {
                            padding: 0.25rem 0;
                        }
                        .fi-batch-pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 0.25rem;
                            padding: 0.2rem 0.45rem;
                            font-size: 0.72rem;
                            font-weight: 600;
                            border-radius: 0.375rem;
                            border-width: 1px;
                        }
                        .fi-batch-pill-total {
                            background-color: #f1f5f9;
                            color: #334155;
                            border-color: #e2e8f0;
                        }
                        :is(.dark) .fi-batch-pill-total {
                            background-color: rgba(255, 255, 255, 0.06);
                            color: #cbd5e1;
                            border-color: rgba(255, 255, 255, 0.1);
                        }
                        .fi-batch-pill-present {
                            background-color: #dcfce7;
                            color: #166534;
                            border-color: #bbf7d0;
                        }
                        :is(.dark) .fi-batch-pill-present {
                            background-color: rgba(22, 163, 74, 0.2);
                            color: #86efac;
                            border-color: rgba(22, 163, 74, 0.3);
                        }
                        .fi-batch-pill-absent {
                            background-color: #fee2e2;
                            color: #991b1b;
                            border-color: #fecaca;
                        }
                        :is(.dark) .fi-batch-pill-absent {
                            background-color: rgba(239, 68, 68, 0.2);
                            color: #fca5a5;
                            border-color: rgba(239, 68, 68, 0.3);
                        }
                        .fi-batch-pill-pending {
                            background-color: #fef3c7;
                            color: #92400e;
                            border-color: #fde68a;
                        }
                        :is(.dark) .fi-batch-pill-pending {
                            background-color: rgba(245, 158, 11, 0.2);
                            color: #fcd34d;
                            border-color: rgba(245, 158, 11, 0.3);
                        }
                        .fi-batch-pill-selected {
                            background-color: #ecfdf5;
                            color: #065f46;
                            border-color: #a7f3d0;
                        }
                        :is(.dark) .fi-batch-pill-selected {
                            background-color: rgba(16, 185, 129, 0.2);
                            color: #6ee7b7;
                            border-color: rgba(16, 185, 129, 0.3);
                        }
                        .fi-batch-pill-rejected {
                            background-color: #fdf2f8;
                            color: #9d174d;
                            border-color: #fbcfe8;
                        }
                        :is(.dark) .fi-batch-pill-rejected {
                            background-color: rgba(236, 72, 153, 0.2);
                            color: #f472b6;
                            border-color: rgba(236, 72, 153, 0.3);
                        }
                    </style>
                '
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn(): \Illuminate\Contracts\View\View => view('filament.admin-greeting')
            );

    }


    
}
