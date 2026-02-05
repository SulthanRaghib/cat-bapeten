<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CAT BAPETEN') }} - Ujian</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;800&display=swap" rel="stylesheet" />

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

    <!-- Custom styles -->
    <style>
        * {
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            margin: 0;
            background: #f5f7f5;
            color: #333;
            padding-top: 100px;
        }

        /* ================= TOPBAR ================= */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            z-index: 1000;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        /* LOGO */
        .topbar-left {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 60px;
            object-fit: contain;
        }

        /* TITLE */
        .topbar h1 {
            font-size: 25px;
            font-weight: 600;
            color: #1f2937;
            text-align: center;
            margin: 0;
        }

        /* ================= TIMER ================= */
        .timer-box {
            background: #f9a825;
            color: #000;
            padding: 8px 16px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 140px;
        }

        .timer-label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .timer-time {
            font-size: 25px;
            font-weight: 800;
            line-height: 1.2;
        }

        /* WARNING */
        .timer-warning {
            background: #d32f2f;
            color: #fff;
        }

        .question-content img {
            max-width: 80%;
            height: auto;
            border-radius: 8px;
            margin: 15px auto;
            display: block;
            border: 1px solid #ddd;
        }

        .question-content p {
            margin-bottom: 1rem;
            line-height: 1.6;
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

        @media (max-width: 768px) {
            .topbar {
                flex-direction: column;
                height: auto;
                padding: 12px;
                gap: 12px;
            }

            .topbar h1 {
                font-size: 18px;
            }

            .timer-time {
                font-size: 22px;
            }

            body {
                padding-top: 140px;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="topbar-left">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="logo">
        </div>

        @isset($examTitle)
            <h1>{{ $examTitle ?: 'Computer Assisted Test' }}</h1>
            
            <div class="timer-box" data-timer-container>
                <span class="timer-label">Sisa Waktu</span>
                <span id="exam-timer" class="timer-time" data-state="normal" data-end-time="{{ $endTime ?? '' }}"
                    wire:ignore>--:--</span>
            </div>
        @else
            <h1>Computer Assisted Test</h1>
            <div class="timer-box">
                <span class="timer-label">Sisa Waktu</span>
                <span class="timer-time">--:--</span>
            </div>
        @endisset
    </header>

    <main style="padding: 20px 0; min-height: calc(100vh - 200px);">
        {{ $slot }}
    </main>

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
