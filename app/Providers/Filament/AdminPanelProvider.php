<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                'panels::body.end',
                fn(): string => Blade::render(<<<'HTML'
                    <script>
                        window.MathJax = {
                            tex: {
                                inlineMath: [['\\(', '\\)'], ['$', '$']],
                                displayMath: [['\\[', '\\]'], ['$$', '$$']],
                                processEscapes: true,
                                processEnvironments: true
                            },
                            options: {
                                // Skip form elements and editors
                                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'annotation', 'annotation-xml', 'input'],
                                // Only render math in specific display containers
                                processHtmlClass: 'math-render-container|question-content|option-text|math-content',
                                // Ignore form/editor areas (NOT modal-content, because view modals need rendering)
                                ignoreHtmlClass: 'ProseMirror|fi-fo-rich-editor|tiptap-wrapper|fi-input|fi-fo-field-wrp'
                            },
                            svg: {
                                fontCache: 'global'
                            },
                            startup: {
                                ready: () => {
                                    MathJax.startup.defaultReady();

                                    // Selective render function - only renders in safe containers
                                    window.renderMathJax = function(container = null) {
                                        if (!window.MathJax || !window.MathJax.typesetPromise) return;

                                        // Find only display containers, avoid form areas
                                        const targets = container
                                            ? [container]
                                            : document.querySelectorAll('.math-render-container, .math-content, .question-content, .option-text');

                                        if (targets.length === 0) return;

                                        // Filter out any that are inside form fields (but NOT view modals)
                                        const safeTargets = Array.from(targets).filter(el => {
                                            // Skip if inside form field wrapper (edit forms)
                                            if (el.closest('.fi-fo-field-wrp')) return false;
                                            // Skip if inside ProseMirror editor
                                            if (el.closest('.ProseMirror')) return false;
                                            return true;
                                        });

                                        if (safeTargets.length > 0) {
                                            MathJax.typesetPromise(safeTargets).catch((err) => console.log('MathJax:', err));
                                        }
                                    };

                                    // Re-render MathJax when Livewire updates (but not in forms)
                                    if (window.Livewire) {
                                        Livewire.hook('morph.updated', ({ el }) => {
                                            // Only render if not inside a form field
                                            if (!el.closest('.fi-fo-field-wrp')) {
                                                setTimeout(window.renderMathJax, 200);
                                            }
                                        });
                                    }

                                    // Watch for modal opens
                                    const observer = new MutationObserver((mutations) => {
                                        mutations.forEach((mutation) => {
                                            if (mutation.addedNodes.length) {
                                                mutation.addedNodes.forEach((node) => {
                                                    if (node.nodeType === 1) {
                                                        // Check if this node or children have math-render-container
                                                        const mathContainer = node.classList?.contains('math-render-container')
                                                            ? node
                                                            : node.querySelector?.('.math-render-container');
                                                        if (mathContainer) {
                                                            setTimeout(() => window.renderMathJax(mathContainer), 300);
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });

                                    observer.observe(document.body, {
                                        childList: true,
                                        subtree: true
                                    });

                                    // Initial render for table only
                                    setTimeout(window.renderMathJax, 500);
                                }
                            }
                        };
                    </script>
                    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js" async></script>

                    <!-- Math Formula Helper Styles -->
                    <style>
                        .math-formula-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            padding: 6px 10px;
                            background: linear-gradient(135deg, #f59e0b, #d97706);
                            color: white;
                            border-radius: 6px;
                            font-size: 12px;
                            font-weight: 500;
                            cursor: pointer;
                            transition: all 0.2s;
                            border: none;
                        }
                        .math-formula-btn:hover {
                            background: linear-gradient(135deg, #d97706, #b45309);
                            transform: translateY(-1px);
                        }
                        .math-picker-modal {
                            position: fixed;
                            inset: 0;
                            background: rgba(0,0,0,0.5);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            z-index: 9999;
                        }
                        .math-picker-content {
                            background: white;
                            border-radius: 12px;
                            padding: 20px;
                            max-width: 600px;
                            width: 90%;
                            max-height: 80vh;
                            overflow-y: auto;
                            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                        }
                        .math-picker-grid {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 8px;
                            margin-top: 12px;
                        }
                        .math-picker-item {
                            padding: 12px 8px;
                            background: #f3f4f6;
                            border: 2px solid transparent;
                            border-radius: 8px;
                            cursor: pointer;
                            text-align: center;
                            transition: all 0.2s;
                        }
                        .math-picker-item:hover {
                            background: #fef3c7;
                            border-color: #f59e0b;
                        }
                        .math-picker-item .formula {
                            font-size: 18px;
                            margin-bottom: 4px;
                        }
                        .math-picker-item .label {
                            font-size: 10px;
                            color: #6b7280;
                        }
                    </style>

                    <!-- LaTeX Chip Copy Handler -->
                    <script>
                        (function() {
                            // Wait for DOM ready
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', initLatexChips);
                            } else {
                                initLatexChips();
                            }

                            function initLatexChips() {
                                document.body.addEventListener('click', function(event) {
                                    var btn = event.target.closest('.latex-chip-btn');
                                    if (!btn) return;

                                    // IMPORTANT: Prevent default behavior
                                    event.preventDefault();
                                    event.stopPropagation();

                                    var formula = btn.getAttribute('data-formula');
                                    if (!formula) {
                                        console.log('No formula found');
                                        return;
                                    }

                                    // Debug log
                                    console.log('Copying formula:', formula);

                                    // Copy to clipboard
                                    navigator.clipboard.writeText(formula).then(function() {
                                        // Visual feedback
                                        btn.classList.add('copied');

                                        // Show copied badge
                                        var badge = document.createElement('span');
                                        badge.style.cssText = 'position:absolute;top:-8px;right:-8px;background:#10b981;color:white;font-size:10px;padding:2px 6px;border-radius:10px;z-index:10;';
                                        badge.textContent = '✓';
                                        btn.style.position = 'relative';
                                        btn.appendChild(badge);

                                        setTimeout(function() {
                                            btn.classList.remove('copied');
                                            if (badge.parentNode) badge.remove();
                                        }, 1500);

                                        console.log('Copied successfully!');
                                    }).catch(function(err) {
                                        console.error('Copy failed:', err);
                                        // Fallback for older browsers
                                        var textarea = document.createElement('textarea');
                                        textarea.value = formula;
                                        textarea.style.position = 'fixed';
                                        textarea.style.left = '-9999px';
                                        document.body.appendChild(textarea);
                                        textarea.select();
                                        try {
                                            document.execCommand('copy');
                                            btn.classList.add('copied');
                                            setTimeout(function() { btn.classList.remove('copied'); }, 1500);
                                        } catch(e) {
                                            console.error('Fallback copy failed');
                                        }
                                        document.body.removeChild(textarea);
                                    });
                                }, true); // Use capture phase
                            }
                        })();
                    </script>
                HTML)
            );
    }
}
