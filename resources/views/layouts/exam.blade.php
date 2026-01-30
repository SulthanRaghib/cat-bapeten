<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CAT BAPETEN') }} - Ujian</title>

    <!-- Tailwind CSS (via CDN for simplicity if Vite not available for front, but using Vite normally) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Note: Alpine.js is bundled with Livewire 3, no need to load separately -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles

    @stack('styles')

    <!-- MathJax Config -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)'],
                    ['$', '$']
                ],
                displayMath: [
                    ['\\[', '\\]'],
                    ['$$', '$$']
                ],
                processEscapes: true,
                processEnvironments: true
            },
            options: {
                processHtmlClass: 'math-render-container|question-content|option-text|math-content',
            },
            svg: {
                fontCache: 'global'
            },
            startup: {
                ready: () => {
                    MathJax.startup.defaultReady();
                    window.renderMathJax = function() {
                        const nodeList = document.querySelectorAll(
                            '.question-content, .option-text, .math-content');
                        if (nodeList.length && window.MathJax) {
                            const elements = Array.from(nodeList);
                            if (typeof MathJax.typesetClear === 'function') {
                                MathJax.typesetClear(elements);
                            }
                            MathJax.typesetPromise(elements).catch((err) => console.log('MathJax:', err));
                        }
                    };
                    setTimeout(window.renderMathJax, 500);
                }
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js" async></script>

    <!-- Custom styles for question content -->
    <style>
        .question-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .question-content p {
            margin-bottom: 1rem;
        }

        .question-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 1rem 0;
        }

        .question-content table td,
        .question-content table th {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            // Setiap kali Livewire selesai update DOM (pindah soal, simpan jawaban)
            Livewire.hook('morph.updated', ({
                el,
                component
            }) => {
                if (window.MathJax) {
                    // Minta MathJax render ulang, tapi gunakan Promise agar tidak bikin lag
                    window.MathJax.typesetPromise().then(() => {
                        console.log('MathJax re-rendered');
                    }).catch((err) => console.log('MathJax error: ' + err.message));
                }
            });
        });
    </script>
</head>

<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <header class="fixed inset-x-0 top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">

        @isset($examTitle)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 p-2 lg:p-4">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-5">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-xl">
                            <svg class="w-9 h-9" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2 3 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 18.5c-3.84-1.07-6.5-4.42-6.5-8.5V8.3l6.5-3.11 6.5 3.11V12c0 4.08-2.66 7.43-6.5 8.5z">
                                </path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-widest text-blue-500 font-bold">Ujian Online</p>
                            <h2 class="text-2xl font-bold text-slate-900">{{ $examTitle }}</h2>
                            <p class="text-sm text-slate-500 font-medium">Badan Pengawas Tenaga Nuklir</p>
                        </div>
                    </div>
                    <div class="timer-shell" data-timer-container>
                        <span class="text-xs font-semibold tracking-wide text-slate-600 uppercase">Sisa
                            Waktu</span>
                        <span id="exam-timer" class="timer-value" data-state="normal" data-end-time="{{ $endTime ?? '' }}"
                            wire:ignore>--:--</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-900">{{ $candidateName ?? '' }}</p>
                        @if (!empty($candidateIdentifier))
                            <p class="text-xs font-medium text-slate-500">ID Peserta: {{ $candidateIdentifier }}</p>
                        @endif
                        @if (isset($answeredCount, $totalQuestions))
                            <p class="text-xs text-slate-400 mt-1">Soal terjawab: {{ $answeredCount }} /
                                {{ $totalQuestions }}</p>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endisset
        </div>
    </header>

    <main class="pt-[180px] pb-32">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts

    <!-- Fix image URLs if hostname mismatch -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fixImageUrls();
        });

        // Also fix after Livewire updates
        document.addEventListener('livewire:navigated', fixImageUrls);

        function fixImageUrls() {
            const currentHost = window.location.origin;
            document.querySelectorAll('.question-content img').forEach(img => {
                const src = img.getAttribute('src');
                if (src) {
                    // Replace localhost or 127.0.0.1 with current host
                    const newSrc = src
                        .replace(/https?:\/\/localhost(:\d+)?/i, currentHost)
                        .replace(/https?:\/\/127\.0\.0\.1(:\d+)?/i, currentHost);
                    if (newSrc !== src) {
                        img.setAttribute('src', newSrc);
                    }
                }
            });
        }

        // Livewire 3 event listener
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(fixImageUrls, 100);
            });
        }
    </script>

    @stack('scripts')
</body>

</html>
