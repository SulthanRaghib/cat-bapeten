@php
    use Illuminate\Support\Str;

    // Helper function to fix image URLs
    if (!function_exists('fixImageUrlsForDisplay')) {
        function fixImageUrlsForDisplay($html)
        {
            if (empty($html)) {
                return '';
            }

            // Get current request base URL
            $baseUrl = request()->getSchemeAndHttpHost();

            // Fix any absolute URLs (http://localhost/storage/..., http://127.0.0.1:8000/storage/...)
            // Convert them to use current base URL
            $html = preg_replace_callback(
                '/src=["\']https?:\/\/[^\/]+\/storage\/([^"\']+)["\']/i',
                function ($matches) use ($baseUrl) {
                    return 'src="' . $baseUrl . '/storage/' . $matches[1] . '"';
                },
                $html,
            );

            return $html;
        }
    }

    $questionHtml = fixImageUrlsForDisplay($record->question_text);
    $examPackage = $record->examPackage;
    $tipe = $examPackage->type ?? 'technical';
    $kunciJawaban = $record->scoring_config['correct'] ?? null;
@endphp

<div class="space-y-6 math-render-container">
    {{-- Question Section --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-amber-900">Soal</h3>
                    <p class="text-xs text-amber-600">Pertanyaan yang akan ditampilkan ke peserta</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="prose prose-sm md:prose-base max-w-none text-gray-700 question-content">
                {!! $questionHtml !!}
            </div>
        </div>
    </div>

    {{-- Options Section --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-blue-900">Pilihan Jawaban</h3>
                    <p class="text-xs text-blue-600">{{ count($record->options) }} opsi tersedia</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse ($record->options as $kode => $optionText)
                    @php
                        $isCorrect = $tipe === 'technical' && $kunciJawaban === $kode;
                        $bobot = $tipe === 'structural' ? $record->scoring_config[$kode] ?? 0 : null;
                        // Handle both array format (old) and string format (new)
                        $rawText = is_array($optionText) ? $optionText['teks'] ?? '' : (string) $optionText;
                        $optionHtml = fixImageUrlsForDisplay($rawText);
                    @endphp

                    <div
                        class="flex items-start gap-4 p-4 rounded-xl border-2 transition-all duration-200 {{ $isCorrect ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-400 shadow-green-100 shadow-md' : 'bg-gray-50 border-gray-200 hover:border-gray-300' }}">
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center w-12 h-12 rounded-xl font-bold text-lg {{ $isCorrect ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg' : 'bg-gray-200 text-gray-600' }}">
                                {{ $kode }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-gray-800 option-text leading-relaxed prose prose-sm max-w-none">
                                @if (!empty(trim($rawText)))
                                    {!! !empty($optionHtml) ? $optionHtml : e($rawText) !!}
                                @else
                                    <span class="text-gray-400 italic">(Teks kosong)</span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($bobot !== null)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        </svg>
                                        Bobot: {{ $bobot }} poin
                                    </span>
                                @endif

                                @if ($isCorrect)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white rounded-full text-xs font-semibold shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Jawaban Benar
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <p>Tidak ada pilihan jawaban</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Scoring Config Section --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-purple-900">Konfigurasi Penilaian</h3>
                    <p class="text-xs text-purple-600">Tipe:
                        {{ $tipe === 'technical' ? 'Teknis (Benar/Salah)' : 'Struktural (Bobot)' }}</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-3 p-4 bg-purple-50 rounded-xl">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-purple-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm font-medium text-purple-900">
                    {!! $manager->formatScoringConfig($record) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .question-content img,
    .option-text img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 1rem 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e5e7eb;
    }

    .question-content p:first-child,
    .option-text p:first-child {
        margin-top: 0;
    }

    .question-content p:last-child,
    .option-text p:last-child {
        margin-bottom: 0;
    }

    .question-content a,
    .option-text a {
        color: #d97706;
        text-decoration: underline;
    }

    .question-content code,
    .option-text code {
        background: #fef3c7;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.9em;
    }

    /* MathJax rendered formulas */
    .question-content mjx-container,
    .option-text mjx-container {
        display: inline-block !important;
        margin: 0 2px;
    }
</style>

<script>
    // Trigger MathJax render when this view loads
    (function() {
        var maxAttempts = 20;
        var attempts = 0;

        function tryRender() {
            attempts++;
            if (window.MathJax && window.MathJax.typesetPromise) {
                var containers = document.querySelectorAll(
                    '.math-render-container, .question-content, .option-text');
                if (containers.length > 0) {
                    MathJax.typesetPromise(Array.from(containers))
                        .then(function() {
                            console.log('MathJax rendered successfully');
                        })
                        .catch(function(err) {
                            console.log('MathJax render error:', err);
                        });
                }
            } else if (attempts < maxAttempts) {
                setTimeout(tryRender, 250);
            }
        }

        // Start rendering after a short delay
        setTimeout(tryRender, 300);
    })();
</script>
