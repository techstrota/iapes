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
use App\Filament\Intern\Pages\Auth\Login;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class InternPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('intern')
            ->path('intern')
            ->login(Login::class)
            ->loginRouteSlug('login')
            ->authPasswordBroker('interns')
            ->authGuard('intern')
            ->brandName('IAPES Intern Portal')
            ->colors([
                'primary' => '#3b82f6', // Stitch Blue / Logo Blue
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
            ->databaseTransactions()
            ->discoverResources(in: app_path('Filament/Intern/Resources'), for: 'App\\Filament\\Intern\\Resources')
            ->discoverPages(in: app_path('Filament/Intern/Pages'), for: 'App\\Filament\\Intern\\Pages')
            ->pages([
                \App\Filament\Intern\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Intern/Widgets'), for: 'App\\Filament\\Intern\\Widgets')
            ->widgets([
                // Widgets are discovered and registered
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

                        /* ── Stitch Stat Overview Cards ── */
                        .fi-wi-stats-overview-stat {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 14px !important;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
                            color: #dae2fd !important;
                            transition: transform 0.2s ease, border-color 0.2s ease !important;
                        }
                        .fi-wi-stats-overview-stat:hover {
                            border-color: rgba(59, 130, 246, 0.45) !important;
                            transform: translateY(-2px);
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
                            overflow: hidden !important;
                        }
                        :is(.dark) .fi-modal-window .fi-section-header,
                        .fi-modal-window .fi-section-header {
                            background-color: rgba(11, 19, 38, 0.7) !important;
                            border-bottom: 1px solid #222a3d !important;
                            padding: 12px 18px !important;
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

                        /* Form Inputs */
                        :is(.dark) .fi-input-wrp,
                        .fi-input-wrp {
                            background-color: #090e1c !important;
                            border: 1px solid #222a3d !important;
                            border-radius: 9px !important;
                            color: #dae2fd !important;
                        }
                        :is(.dark) .fi-input-wrp:focus-within,
                        .fi-input-wrp:focus-within {
                            border-color: #3b82f6 !important;
                            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
                        }
                        :is(.dark) input,
                        :is(.dark) textarea,
                        :is(.dark) select,
                        input,
                        textarea,
                        select {
                            color: #ffffff !important;
                        }

                        /* Buttons */
                        :is(.dark) .fi-btn-color-primary,
                        .fi-btn-color-primary,
                        :is(.dark) button[type="submit"]:not(.fi-btn-color-gray) {
                            background-color: #1e40af !important;
                            border: 1px solid #3b82f6 !important;
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            border-radius: 9px !important;
                            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.45) !important;
                            transition: background-color 0.15s ease !important;
                        }
                        :is(.dark) .fi-btn-color-primary:hover,
                        .fi-btn-color-primary:hover,
                        :is(.dark) button[type="submit"]:not(.fi-btn-color-gray):hover {
                            background-color: #1d4ed8 !important;
                        }

                        /* ── Stitch Infolists & Sections ── */
                        :is(.dark) .fi-infolist .fi-section,
                        .fi-infolist .fi-section,
                        :is(.dark) .fi-page .fi-section,
                        .fi-page .fi-section {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 16px !important;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
                            overflow: visible !important;
                            position: relative;
                        }
                        :is(.dark) .fi-infolist .fi-section-header,
                        .fi-infolist .fi-section-header,
                        :is(.dark) .fi-page .fi-section-header,
                        .fi-page .fi-section-header {
                            background-color: #0b1326 !important;
                            border-bottom: 1.5px solid #222a3d !important;
                            padding: 14px 20px !important;
                            border-top-left-radius: 15px !important;
                            border-top-right-radius: 15px !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-header-heading,
                        .fi-infolist .fi-section-header-heading,
                        :is(.dark) .fi-page .fi-section-header-heading,
                        .fi-page .fi-section-header-heading {
                            color: #ffffff !important;
                            font-size: 15px !important;
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                        }
                        :is(.dark) .fi-infolist .fi-section-content,
                        .fi-infolist .fi-section-content,
                        :is(.dark) .fi-page .fi-section-content,
                        .fi-page .fi-section-content {
                            background-color: #131b2e !important;
                            padding: 22px !important;
                        }
                        :is(.dark) .fi-infolist .fi-in-entry-label,
                        .fi-infolist .fi-in-entry-label {
                            color: #8e909f !important;
                            font-size: 11px !important;
                            text-transform: uppercase !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.05em !important;
                        }
                        :is(.dark) .fi-infolist .fi-in-text,
                        .fi-infolist .fi-in-text {
                            color: #dae2fd !important;
                        }

                        /* ── Attendance Cards Styling ── */
                        .fi-attendance-grid .fi-ta-record {
                            background-color: #131b2e !important;
                            border: 1.5px solid #222a3d !important;
                            border-radius: 14px !important;
                            transition: all 0.2s ease !important;
                        }
                        .fi-attendance-grid .fi-ta-record:hover {
                            border-color: rgba(59, 130, 246, 0.45) !important;
                            transform: translateY(-2px);
                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
                        }

                        /* Smooth Scrollbars */
                        ::-webkit-scrollbar {
                            width: 7px;
                            height: 7px;
                        }
                        ::-webkit-scrollbar-track {
                            background: #060e20;
                        }
                        ::-webkit-scrollbar-thumb {
                            background: #222a3d;
                            border-radius: 9999px;
                        }
                        ::-webkit-scrollbar-thumb:hover {
                            background: #3b82f6;
                        }
                    </style>
HTML
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn(): \Illuminate\Contracts\View\View => view('filament.intern-greeting')
            );
    }
}
