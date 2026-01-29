<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CAT BAPETEN') }} - Ujian</title>

    <!-- Tailwind CSS (via CDN for simplicity if Vite not available for front, but using Vite normally) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles

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
                        const elements = document.querySelectorAll(
                            '.question-content, .option-text, .math-content');
                        if (elements.length && window.MathJax) {
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
</head>

<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800 tracking-tight">
                CAT BAPETEN
            </h1>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->nip }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="py-6">
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
</body>

</html>
