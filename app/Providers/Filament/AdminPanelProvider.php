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
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
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
                fn(): string => <<<'HTML'
                    <script>
                        // Permanently force Dark Theme and disable Light Theme
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                        window.addEventListener('DOMContentLoaded', () => {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        });
                        window.addEventListener('storage', () => {
                            if (localStorage.getItem('theme') !== 'dark') {
                                localStorage.setItem('theme', 'dark');
                                document.documentElement.classList.add('dark');
                            }
                        });
                    </script>
                    <style>
                        /* Disable Light Theme Completely - Hide Theme Switcher */
                        .fi-theme-switcher,
                        [data-theme-switcher],
                        button[aria-label*="theme" i],
                        button[title*="theme" i] {
                            display: none !important;
                        }

                        html {
                            color-scheme: dark !important;
                        }

                        html:not(.dark),
                        html:not(.dark) body,
                        html:not(.dark) .fi-layout,
                        html:not(.dark) .fi-main {
                            background-color: #0b1326 !important;
                            color: #dae2fd !important;
                        }

                        /* Stitch Enterprise Dark Theme Palette */
                        :is(.dark) {
                            --stitch-bg: #0b1326;
                            --stitch-surface: #131b2e;
                            --stitch-surface-container: #171f33;
                            --stitch-surface-high: #222a3d;
                            --stitch-surface-highest: #2d3449;
                            --stitch-surface-lowest: #060e20;
                            --stitch-on-surface: #dae2fd;
                            --stitch-on-surface-variant: #c4c5d5;
                            --stitch-outline: #8e909f;
                            --stitch-outline-variant: #2d3449;
                            --stitch-border: #222a3d;
                            --stitch-primary: #b8c4ff;
                            --stitch-primary-container: #1e40af;
                        }

                        :is(.dark) body,
                        :is(.dark) .fi-layout,
                        :is(.dark) .fi-main {
                            background-color: #0b1326 !important;
                            color: #dae2fd !important;
                        }

                        :is(.dark) .fi-sidebar {
                            background-color: #060e20 !important;
                            border-color: #222a3d !important;
                        }

                        :is(.dark) .fi-topbar {
                            background-color: rgba(11, 19, 38, 0.9) !important;
                            backdrop-filter: blur(12px) !important;
                            border-color: #222a3d !important;
                        }

                        /* Align selection checkbox to top-left of candidate card */
                        .fi-ta-content-grid .fi-ta-record > div.flex {
                            align-items: flex-start !important;
                        }
                        /* Selection checkbox styling */
                        .fi-ta-content-grid .fi-ta-record-checkbox {
                            margin-top: 0.95rem !important;
                            margin-inline-start: 0.5rem !important;
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
                            background-color: #0b1326 !important;
                            border: 1.5px solid #1c2638 !important;
                            border-radius: 1rem !important;
                            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
                            transition: all 0.2s ease !important;
                            position: relative !important;
                            isolation: isolate !important;
                            z-index: 1 !important;
                            overflow: hidden !important;
                            min-width: 0 !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        }
                        .fi-ta-content-grid .fi-ta-record:hover {
                            border-color: #2e3d57 !important;
                            box-shadow: 0 8px 30px -4px rgba(0, 0, 0, 0.6) !important;
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
                            align-items: center !important;
                            justify-content: flex-end !important;
                            flex-wrap: wrap !important;
                            gap: 0.45rem !important;
                            width: 100% !important;
                            margin-top: 0.75rem !important;
                            padding-top: 0.65rem !important;
                            border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
                            box-sizing: border-box !important;
                        }
                        /* Each action button in card */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action {
                            flex: 0 1 auto !important;
                            min-width: 0 !important;
                            box-sizing: border-box !important;
                        }
                        /* Fill button width with centered text and zero cut-off */
                        .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action .fi-btn {
                            white-space: nowrap !important;
                            font-size: 0.78rem !important;
                            padding: 0.35rem 0.6rem !important;
                            box-sizing: border-box !important;
                            border-radius: 0.5rem !important;
                        }
                        @media (max-width: 420px) {
                            .fi-ta-content-grid .fi-ta-record .fi-ta-actions .fi-ta-action {
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

                        /* ────────────────────────── Stitch Global Tables, Tabs & Empty State ────────────────────────── */
                        .fi-ta-ctn,
                        .fi-wi-table {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 16px !important;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
                            overflow: hidden !important;
                        }
                        .fi-ta-header,
                        .fi-ta-header-ctn {
                            background-color: #131b2e !important;
                            border-bottom: 1.5px solid #1c2638 !important;
                            padding: 1rem 1.25rem !important;
                        }
                        .fi-ta-header-heading {
                            color: #ffffff !important;
                            font-size: 0.95rem !important;
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                        }
                        .fi-ta-header-description {
                            color: #8e909f !important;
                            font-size: 0.8rem !important;
                        }
                        .fi-ta-content {
                            background-color: #0b1326 !important;
                        }
                        .fi-ta-row {
                            transition: background-color 0.15s ease !important;
                        }
                        .fi-ta-row:hover {
                            background-color: rgba(255, 255, 255, 0.03) !important;
                        }
                        .fi-ta-cell {
                            border-bottom: 1px solid rgba(34, 42, 61, 0.6) !important;
                            color: #dae2fd !important;
                            font-size: 0.85rem !important;
                        }
                        .fi-ta-pagination {
                            background-color: #131b2e !important;
                            border-top: 1px solid #1c2638 !important;
                        }

                        /* Global Stitch Tabs */
                        .fi-tabs {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 14px !important;
                            padding: 6px !important;
                            gap: 6px !important;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            flex-wrap: wrap !important;
                        }
                        .fi-tabs-item {
                            border-radius: 10px !important;
                            padding: 8px 16px !important;
                            font-size: 13px !important;
                            font-weight: 600 !important;
                            color: #8e909f !important;
                            border: none !important;
                            transition: all 0.15s ease !important;
                            background-color: transparent !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                        }
                        .fi-tabs-item:hover {
                            background-color: #171f33 !important;
                            color: #dae2fd !important;
                        }
                        .fi-tabs-item.fi-active,
                        .fi-tabs-item[aria-selected="true"] {
                            background-color: #1e40af !important;
                            color: #ffffff !important;
                            border: 1px solid #3b82f6 !important;
                            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
                            font-weight: 700 !important;
                        }
                        .fi-tabs-item.fi-active svg,
                        .fi-tabs-item[aria-selected="true"] svg,
                        .fi-tabs-item.fi-active .fi-tabs-item-icon,
                        .fi-tabs-item[aria-selected="true"] .fi-tabs-item-icon {
                            color: #ffffff !important;
                        }
                        .fi-tabs-item .fi-badge {
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            border-radius: 9999px !important;
                            padding: 2px 7px !important;
                            background-color: rgba(255, 255, 255, 0.1) !important;
                            color: #dae2fd !important;
                            border: 1px solid rgba(255, 255, 255, 0.15) !important;
                        }
                        .fi-tabs-item.fi-active .fi-badge,
                        .fi-tabs-item[aria-selected="true"] .fi-badge {
                            background-color: rgba(255, 255, 255, 0.25) !important;
                            color: #ffffff !important;
                            border-color: rgba(255, 255, 255, 0.4) !important;
                        }

                        /* Global Search & Filter Controls */
                        .fi-ta-search-field .fi-input-wrp {
                            background-color: #090e1c !important;
                            border: 1px solid #222a3d !important;
                            border-radius: 10px !important;
                            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4) !important;
                        }
                        .fi-ta-search-field input {
                            color: #dae2fd !important;
                            font-size: 13px !important;
                        }
                        .fi-ta-search-field input::placeholder {
                            color: #8e909f !important;
                        }
                        .fi-ta-search-field .fi-input-wrp:focus-within {
                            border-color: #3b82f6 !important;
                            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
                        }
                        .fi-ta-filters-trigger-btn,
                        .fi-ta-filter-trigger-action button,
                        .fi-ta-filter-trigger button {
                            background-color: #090e1c !important;
                            border: 1px solid #222a3d !important;
                            border-radius: 10px !important;
                            color: #dae2fd !important;
                            transition: all 0.15s ease !important;
                        }
                        .fi-ta-filters-trigger-btn:hover,
                        .fi-ta-filter-trigger-action button:hover {
                            border-color: #3b82f6 !important;
                            color: #ffffff !important;
                        }

                        /* Global Empty State */
                        .fi-ta-empty-state {
                            background-color: #131b2e !important;
                            border-radius: 16px !important;
                            padding: 50px 24px !important;
                        }
                        .fi-ta-empty-state-icon-ctn {
                            background-color: rgba(99, 102, 241, 0.15) !important;
                            border: 1px solid rgba(99, 102, 241, 0.3) !important;
                            color: #b8c4ff !important;
                            width: 58px !important;
                            height: 58px !important;
                            border-radius: 16px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            margin: 0 auto 16px auto !important;
                        }
                        .fi-ta-empty-state-icon-ctn svg {
                            width: 28px !important;
                            height: 28px !important;
                            color: #b8c4ff !important;
                        }
                        .fi-ta-empty-state-heading {
                            color: #ffffff !important;
                            font-size: 18px !important;
                            font-weight: 700 !important;
                            margin-bottom: 6px !important;
                        }
                        .fi-ta-empty-state-description {
                            color: #8e909f !important;
                            font-size: 13px !important;
                            max-width: 440px !important;
                            margin: 0 auto 18px auto !important;
                            line-height: 1.5 !important;
                        }
                        .fi-ta-empty-state-actions .fi-btn {
                            background-color: #1e40af !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            border-radius: 10px !important;
                            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
                            padding: 8px 18px !important;
                        }
                        .fi-ta-empty-state-actions .fi-btn:hover {
                            background-color: #1d4ed8 !important;
                        }

                        .fi-wi-stats-overview-stat {
                            background-color: #131b2e !important;
                            border: 1px solid #222a3d !important;
                            border-radius: 14px !important;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
                            color: #dae2fd !important;
                        }
                        .fi-wi-stats-overview-stat-value {
                            color: #ffffff !important;
                            font-weight: 800 !important;
                        }
                        .fi-wi-stats-overview-stat-label {
                            color: #8e909f !important;
                            text-transform: uppercase !important;
                            font-weight: 700 !important;
                            font-size: 0.72rem !important;
                            letter-spacing: 0.05em !important;
                        }

                        /* ── Stitch Modals & Dialogs ── */
                        :is(.dark) .fi-modal-window,
                        .fi-modal-window {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 16px !important;
                            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8) !important;
                            color: #dae2fd !important;
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-modal-header,
                        .fi-modal-header {
                            background-color: #0b1326 !important;
                            border-bottom: 1.5px solid #222a3d !important;
                            padding: 16px 24px !important;
                        }
                        :is(.dark) .fi-modal-heading,
                        .fi-modal-heading {
                            color: #ffffff !important;
                            font-size: 1.15rem !important;
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                        }
                        :is(.dark) .fi-modal-description,
                        .fi-modal-description {
                            color: #8e909f !important;
                            font-size: 0.82rem !important;
                        }
                        :is(.dark) .fi-modal-content,
                        .fi-modal-content {
                            background-color: #131b2e !important;
                            padding: 22px !important;
                        }
                        :is(.dark) .fi-modal-footer,
                        .fi-modal-footer {
                            background-color: #0b1326 !important;
                            border-top: 1.5px solid #222a3d !important;
                            padding: 14px 24px !important;
                        }

                        /* Modal Inner Sections */
                        :is(.dark) .fi-modal-window .fi-section,
                        .fi-modal-window .fi-section {
                            background-color: #090e1c !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 12px !important;
                            overflow: visible !important;
                        }
                        :is(.dark) .fi-modal-window .fi-section-header,
                        .fi-modal-window .fi-section-header {
                            background-color: rgba(11, 19, 38, 0.7) !important;
                            border-bottom: 1px solid #222a3d !important;
                            padding: 12px 18px !important;
                            border-top-left-radius: 11px !important;
                            border-top-right-radius: 11px !important;
                        }
                        :is(.dark) .fi-modal-window .fi-section-header-heading,
                        .fi-modal-window .fi-section-header-heading {
                            color: #dae2fd !important;
                            font-size: 0.88rem !important;
                            font-weight: 700 !important;
                        }
                        :is(.dark) .fi-modal-window .fi-section-content,
                        .fi-modal-window .fi-section-content {
                            padding: 16px 18px !important;
                        }

                        /* Modal Form Inputs */
                        :is(.dark) .fi-modal-window .fi-input-wrp,
                        .fi-modal-window .fi-input-wrp {
                            background-color: #131b2e !important;
                            border: 1px solid #222a3d !important;
                            border-radius: 9px !important;
                            color: #dae2fd !important;
                        }
                        :is(.dark) .fi-modal-window input,
                        :is(.dark) .fi-modal-window textarea,
                        :is(.dark) .fi-modal-window select,
                        .fi-modal-window input,
                        .fi-modal-window textarea,
                        .fi-modal-window select {
                            color: #ffffff !important;
                        }

                        /* Modal Buttons - Fix Stark Orange Buttons */
                        :is(.dark) .fi-modal-footer .fi-btn-color-primary,
                        :is(.dark) .fi-modal .fi-btn-color-primary,
                        .fi-modal-footer .fi-btn-color-primary,
                        .fi-modal .fi-btn-color-primary,
                        :is(.dark) .fi-modal-footer button[type="submit"],
                        .fi-modal-footer button[type="submit"] {
                            background-color: #1e40af !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            border-radius: 9px !important;
                            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
                            padding: 8px 18px !important;
                            transition: background-color 0.15s ease !important;
                        }
                        :is(.dark) .fi-modal-footer .fi-btn-color-primary:hover,
                        :is(.dark) .fi-modal-footer button[type="submit"]:hover,
                        .fi-modal-footer .fi-btn-color-primary:hover,
                        .fi-modal-footer button[type="submit"]:hover {
                            background-color: #1d4ed8 !important;
                        }
                        :is(.dark) .fi-modal-footer .fi-btn-color-gray,
                        .fi-modal-footer .fi-btn-color-gray {
                            background-color: #171f33 !important;
                            border: 1px solid #222a3d !important;
                            color: #cbd5e1 !important;
                            font-weight: 600 !important;
                            border-radius: 9px !important;
                            padding: 8px 16px !important;
                            transition: all 0.15s ease !important;
                        }
                        :is(.dark) .fi-modal-footer .fi-btn-color-gray:hover,
                        .fi-modal-footer .fi-btn-color-gray:hover {
                            border-color: #3b82f6 !important;
                            color: #ffffff !important;
                        }

                        /* ── Form Section Containers ── */
                        :is(.dark) .fi-page form .fi-section,
                        .fi-page form .fi-section {
                            background-color: #111a2e !important;
                            border: 1.5px solid #1e2a42 !important;
                            border-radius: 16px !important;
                            box-shadow: 0 8px 30px -4px rgba(0, 0, 0, 0.45) !important;
                            overflow: visible !important;
                            position: relative;
                        }
                        :is(.dark) .fi-page form .fi-section-header,
                        .fi-page form .fi-section-header {
                            background: linear-gradient(180deg, #152037 0%, #0f182c 100%) !important;
                            border-bottom: 1.5px solid #1e2a42 !important;
                            padding: 16px 24px !important;
                            border-top-left-radius: 15px !important;
                            border-top-right-radius: 15px !important;
                        }

                        /* Ensure any section containing an active or focused dropdown sits above subsequent sections */
                        :is(.dark) .fi-page form .fi-section:focus-within,
                        .fi-page form .fi-section:focus-within,
                        :is(.dark) .fi-page form .fi-section:has(.choices.is-open),
                        .fi-page form .fi-section:has(.choices.is-open),
                        :is(.dark) .fi-page form .fi-section:has(.is-open),
                        .fi-page form .fi-section:has(.is-open),
                        :is(.dark) .fi-page form .fi-section:has([aria-expanded="true"]),
                        .fi-page form .fi-section:has([aria-expanded="true"]) {
                            position: relative !important;
                            z-index: 40 !important;
                        }

                        :is(.dark) .fi-page form .fi-section-content-ctn,
                        .fi-page form .fi-section-content-ctn,
                        :is(.dark) .fi-page form .fi-section-content,
                        .fi-page form .fi-section-content {
                            overflow: visible !important;
                        }

                        /* ── Choices.js & Select Dropdown Styling & High Stacking ── */
                        .fi-fo-select,
                        .choices {
                            position: relative !important;
                        }

                        .fi-fo-select:focus-within,
                        .choices.is-open {
                            z-index: 45 !important;
                        }

                        .choices__list--dropdown,
                        .choices.is-open .choices__list--dropdown,
                        .is-active.choices__list--dropdown {
                            position: absolute !important;
                            z-index: 9999 !important;
                            background-color: #0b1224 !important;
                            border: 1.5px solid #2d3b55 !important;
                            border-radius: 10px !important;
                            box-shadow: 0 16px 40px -4px rgba(0, 0, 0, 0.85) !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__list,
                        .choices__list--dropdown .choices__list {
                            background-color: #0b1224 !important;
                            max-height: 280px !important;
                            overflow-y: auto !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__item,
                        .choices__list--dropdown .choices__item {
                            color: #dae2fd !important;
                            padding: 10px 14px !important;
                            font-size: 0.88rem !important;
                            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__item:last-child,
                        .choices__list--dropdown .choices__item:last-child {
                            border-bottom: none !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__item--selectable.is-highlighted,
                        .choices__list--dropdown .choices__item--selectable.is-highlighted {
                            background-color: #1e40af !important;
                            color: #ffffff !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__input,
                        .choices__list--dropdown .choices__input {
                            background-color: #090e1c !important;
                            border: 1px solid #222a3d !important;
                            color: #ffffff !important;
                            border-radius: 8px !important;
                            margin: 8px !important;
                            width: calc(100% - 16px) !important;
                            padding: 8px 12px !important;
                        }

                        :is(.dark) .choices__list--dropdown .choices__placeholder,
                        .choices__list--dropdown .choices__placeholder {
                            color: #8e909f !important;
                        }

                        /* Choices selected tags in multi-select */
                        :is(.dark) .choices__list--multiple .choices__item,
                        .choices__list--multiple .choices__item {
                            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            border-radius: 6px !important;
                            font-size: 0.8rem !important;
                            font-weight: 600 !important;
                            padding: 3px 8px !important;
                        }
                        :is(.dark) .choices__list--multiple .choices__item .choices__button,
                        .choices__list--multiple .choices__item .choices__button {
                            border-inline-start: 1px solid rgba(255, 255, 255, 0.3) !important;
                        }
                        :is(.dark) .fi-page form .fi-section-header-heading,
                        .fi-page form .fi-section-header-heading {
                            color: #ffffff !important;
                            font-size: 1.05rem !important;
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                        }
                        :is(.dark) .fi-page form .fi-section-header-heading svg,
                        .fi-page form .fi-section-header-heading svg {
                            color: #60a5fa !important;
                        }
                        :is(.dark) .fi-page form .fi-section-header-description,
                        .fi-page form .fi-section-header-description {
                            color: #94a3b8 !important;
                            font-size: 0.85rem !important;
                            margin-top: 2px !important;
                        }
                        :is(.dark) .fi-page form .fi-section-content,
                        .fi-page form .fi-section-content {
                            background-color: #111a2e !important;
                            padding: 24px !important;
                        }

                        /* ── Form Field Labels & Required Markers ── */
                        :is(.dark) .fi-fo-field-wrp-label span,
                        .fi-fo-field-wrp-label span {
                            color: #dae2fd !important;
                            font-size: 0.82rem !important;
                            font-weight: 600 !important;
                            letter-spacing: 0.01em !important;
                        }
                        :is(.dark) .fi-fo-field-wrp-label sup,
                        .fi-fo-field-wrp-label sup {
                            color: #f87171 !important;
                            font-weight: 700 !important;
                        }
                        :is(.dark) .fi-fo-field-wrp-helper-text,
                        .fi-fo-field-wrp-helper-text {
                            color: #8e909f !important;
                            font-size: 0.78rem !important;
                        }

                        /* ── Form Input Fields ── */
                        :is(.dark) .fi-page form .fi-input-wrp,
                        .fi-page form .fi-input-wrp {
                            background-color: #090e1c !important;
                            border: 1.5px solid #1e293b !important;
                            border-radius: 10px !important;
                            transition: all 0.2s ease !important;
                        }
                        :is(.dark) .fi-page form .fi-input-wrp:hover,
                        .fi-page form .fi-input-wrp:hover {
                            border-color: #2d3b55 !important;
                        }
                        :is(.dark) .fi-page form .fi-input-wrp:focus-within,
                        .fi-page form .fi-input-wrp:focus-within {
                            border-color: #3b82f6 !important;
                            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.22) !important;
                            background-color: #0b1224 !important;
                        }
                        :is(.dark) .fi-page form input,
                        :is(.dark) .fi-page form select,
                        :is(.dark) .fi-page form textarea,
                        .fi-page form input,
                        .fi-page form select,
                        .fi-page form textarea {
                            color: #ffffff !important;
                            font-size: 0.88rem !important;
                        }
                        :is(.dark) .fi-page form input::placeholder,
                        :is(.dark) .fi-page form textarea::placeholder,
                        .fi-page form input::placeholder,
                        .fi-page form textarea::placeholder {
                            color: #55627a !important;
                        }
                        :is(.dark) .fi-page form .fi-input-wrp-prefix,
                        :is(.dark) .fi-page form .fi-input-wrp-suffix,
                        .fi-page form .fi-input-wrp-prefix,
                        .fi-page form .fi-input-wrp-suffix {
                            color: #60a5fa !important;
                        }
                        :is(.dark) .fi-page form .fi-input-wrp-prefix svg,
                        :is(.dark) .fi-page form .fi-input-wrp-suffix svg,
                        .fi-page form .fi-input-wrp-prefix svg,
                        .fi-page form .fi-input-wrp-suffix svg {
                            color: #60a5fa !important;
                        }
                        :is(.dark) .fi-page form .fi-input-wrp:has(input:disabled),
                        .fi-page form .fi-input-wrp:has(input:disabled) {
                            background-color: rgba(9, 14, 28, 0.6) !important;
                            border-color: rgba(30, 41, 59, 0.6) !important;
                            opacity: 0.8 !important;
                        }
                        :is(.dark) .fi-page form input:disabled,
                        .fi-page form input:disabled {
                            color: #94a3b8 !important;
                        }

                        /* ── File Upload (FilePond Dropzone) ── */
                        :is(.dark) .filepond--panel-root {
                            background-color: #090e1c !important;
                            border: 1.5px dashed #22314e !important;
                            border-radius: 12px !important;
                            transition: all 0.2s ease !important;
                        }
                        :is(.dark) .filepond--root:hover .filepond--panel-root {
                            border-color: #3b82f6 !important;
                            background-color: rgba(59, 130, 246, 0.04) !important;
                        }
                        :is(.dark) .filepond--drop-label {
                            color: #94a3b8 !important;
                        }
                        :is(.dark) .filepond--drop-label label span {
                            color: #60a5fa !important;
                            font-weight: 600 !important;
                        }

                        /* ── Form Bottom Action Buttons ── */
                        :is(.dark) .fi-page form .fi-form-actions,
                        .fi-page form .fi-form-actions {
                            display: flex !important;
                            align-items: center !important;
                            gap: 10px !important;
                            margin-top: 1.5rem !important;
                            padding-top: 1rem !important;
                        }
                        :is(.dark) .fi-page form .fi-form-actions .fi-btn-color-primary,
                        .fi-page form .fi-form-actions .fi-btn-color-primary,
                        :is(.dark) .fi-page form button[type="submit"].fi-btn-color-primary,
                        .fi-page form button[type="submit"].fi-btn-color-primary {
                            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            border-radius: 10px !important;
                            padding: 9px 22px !important;
                            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;
                            transition: all 0.15s ease !important;
                        }
                        :is(.dark) .fi-page form .fi-form-actions .fi-btn-color-primary:hover,
                        .fi-page form .fi-form-actions .fi-btn-color-primary:hover {
                            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
                            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.55) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page form .fi-form-actions .fi-btn-color-gray,
                        .fi-page form .fi-form-actions .fi-btn-color-gray {
                            background-color: #131b2e !important;
                            border: 1px solid #233149 !important;
                            color: #cbd5e1 !important;
                            font-weight: 600 !important;
                            border-radius: 10px !important;
                            padding: 9px 20px !important;
                            transition: all 0.15s ease !important;
                        }
                        :is(.dark) .fi-page form .fi-form-actions .fi-btn-color-gray:hover,
                        .fi-page form .fi-form-actions .fi-btn-color-gray:hover {
                            border-color: #3b82f6 !important;
                            color: #ffffff !important;
                            background-color: #172238 !important;
                        }

                        /* ── View Candidate Infolists & Sections ── */
                        :is(.dark) .fi-infolist .fi-section,
                        .fi-infolist .fi-section {
                            background-color: #111a2e !important;
                            border: 1.5px solid #1e2a42 !important;
                            border-radius: 16px !important;
                            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.4) !important;
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-header,
                        .fi-infolist .fi-section-header {
                            background: linear-gradient(180deg, #152037 0%, #0f182c 100%) !important;
                            border-bottom: 1.5px solid #1e2a42 !important;
                            padding: 14px 22px !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-header-heading,
                        .fi-infolist .fi-section-header-heading {
                            color: #ffffff !important;
                            font-size: 15px !important;
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-header-heading svg,
                        .fi-infolist .fi-section-header-heading svg {
                            color: #60a5fa !important;
                            width: 1.25rem !important;
                            height: 1.25rem !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-content,
                        .fi-infolist .fi-section-content {
                            background-color: #111a2e !important;
                            padding: 22px !important;
                        }
                        :is(.dark) .fi-infolist .fi-in-entry-wrp-label span,
                        :is(.dark) .fi-infolist dt.fi-in-entry-wrp-label span,
                        :is(.dark) .fi-infolist .fi-in-entry-label,
                        .fi-infolist .fi-in-entry-wrp-label span {
                            color: #8e909f !important;
                            font-size: 0.72rem !important;
                            text-transform: uppercase !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.05em !important;
                        }
                        :is(.dark) .fi-infolist .fi-in-text,
                        :is(.dark) .fi-infolist .fi-in-text-item,
                        :is(.dark) .fi-infolist dd.fi-in-entry-wrp-content,
                        .fi-infolist .fi-in-text {
                            color: #f1f5f9 !important;
                            font-size: 0.92rem !important;
                            font-weight: 500 !important;
                        }
                        :is(.dark) .fi-infolist .fi-in-text svg,
                        :is(.dark) .fi-infolist .fi-in-entry-wrp svg,
                        .fi-infolist .fi-in-text svg {
                            color: #60a5fa !important;
                        }
                        :is(.dark) .fi-infolist .fi-badge {
                            font-weight: 700 !important;
                            border-radius: 9999px !important;
                            padding: 0.2rem 0.65rem !important;
                        }
                        :is(.dark) .fi-infolist button.fi-in-copy-btn,
                        :is(.dark) .fi-infolist button:has(svg) {
                            color: #94a3b8 !important;
                            transition: all 0.15s ease !important;
                        }
                        :is(.dark) .fi-infolist button.fi-in-copy-btn:hover,
                        :is(.dark) .fi-infolist button:has(svg):hover {
                            color: #38bdf8 !important;
                        }

                        /* ── View Page & Resource Header Action Buttons ── */
                        :is(.dark) .fi-page-header-actions .fi-btn,
                        .fi-page-header-actions .fi-btn {
                            border-radius: 10px !important;
                            font-weight: 700 !important;
                            font-size: 0.84rem !important;
                            padding: 8px 16px !important;
                            transition: all 0.15s ease !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 6px !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-primary,
                        .fi-page-header-actions .fi-btn-color-primary {
                            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-primary:hover {
                            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
                            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.55) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-success,
                        .fi-page-header-actions .fi-btn-color-success {
                            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
                            border: 1px solid #10b981 !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-success:hover {
                            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.5) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-warning,
                        .fi-page-header-actions .fi-btn-color-warning {
                            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
                            border: 1px solid #f59e0b !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-warning:hover {
                            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.5) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-danger,
                        .fi-page-header-actions .fi-btn-color-danger {
                            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
                            border: 1px solid #ef4444 !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-danger:hover {
                            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
                            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.5) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-info,
                        .fi-page-header-actions .fi-btn-color-info {
                            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
                            border: 1px solid #38bdf8 !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-info:hover {
                            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%) !important;
                            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.5) !important;
                            transform: translateY(-1px) !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-gray,
                        .fi-page-header-actions .fi-btn-color-gray {
                            background-color: #131b2e !important;
                            border: 1px solid #233149 !important;
                            color: #cbd5e1 !important;
                        }
                        :is(.dark) .fi-page-header-actions .fi-btn-color-gray:hover {
                            border-color: #3b82f6 !important;
                            color: #ffffff !important;
                            background-color: #172238 !important;
                            transform: translateY(-1px) !important;
                        }

                        /* ── Relation Manager on View Page ── */
                        :is(.dark) .fi-resource-relation-manager,
                        .fi-resource-relation-manager {
                            margin-top: 2rem !important;
                        }
                        :is(.dark) .fi-resource-relation-manager .fi-ta-ctn,
                        .fi-resource-relation-manager .fi-ta-ctn {
                            background-color: #111a2e !important;
                            border: 1.5px solid #1e2a42 !important;
                            border-radius: 16px !important;
                            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.4) !important;
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-resource-relation-manager .fi-ta-header,
                        .fi-resource-relation-manager .fi-ta-header {
                            background: linear-gradient(180deg, #152037 0%, #0f182c 100%) !important;
                            border-bottom: 1.5px solid #1e2a42 !important;
                            padding: 14px 22px !important;
                        }
                        :is(.dark) .fi-resource-relation-manager .fi-ta-header-heading,
                        .fi-resource-relation-manager .fi-ta-header-heading {
                            color: #ffffff !important;
                            font-size: 15px !important;
                            font-weight: 700 !important;
                        }
                    </style>
HTML
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn(): \Illuminate\Contracts\View\View => view('filament.admin-greeting')
            );

    }


    
}
