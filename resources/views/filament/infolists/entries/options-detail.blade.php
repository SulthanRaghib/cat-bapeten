@php
    $options = $getState();
    $record = $getRecord();
    $examPackage = $record->examPackage;
@endphp

<div class="options-detail-content" x-data="{
    renderMath() {
        if (window.MathJax && window.MathJax.typesetPromise) {
            this.$nextTick(() => {
                setTimeout(() => {
                    MathJax.typesetPromise([this.$el]).catch((err) => console.error('MathJax render error:', err));
                }, 300);
            });
        }
    }
}" x-init="renderMath()"
    @livewire:navigated.window="renderMath()">

    <div class="space-y-3">
        @foreach ($options as $kode => $option)
            @php
                $isCorrect = false;
                if ($examPackage->type === 'technical') {
                    $isCorrect = $record->scoring_config['kunci'] ?? null === $kode;
                }
                $bobot = $examPackage->type === 'structural' ? $record->scoring_config['bobot'][$kode] ?? 0 : null;
            @endphp

            <div
                class="flex items-start gap-3 p-4 rounded-lg border-2 transition-colors
                {{ $isCorrect ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-200' }}">

                <div class="flex-shrink-0">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm
                        {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                        {{ $kode }}
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm text-gray-800 option-text leading-relaxed">
                        {!! $option['teks'] ?? '' !!}
                    </div>

                    @if ($bobot !== null)
                        <div
                            class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 text-amber-800 rounded text-xs font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Bobot: {{ $bobot }}
                        </div>
                    @endif

                    @if ($isCorrect)
                        <div
                            class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Jawaban Benar
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .options-detail-content img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 0.75rem 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .options-detail-content .option-text>p:first-child {
            margin-top: 0;
        }

        .options-detail-content .option-text>p:last-child {
            margin-bottom: 0;
        }
    </style>
</div>
