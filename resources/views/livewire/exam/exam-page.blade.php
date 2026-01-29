<div class="space-y-6">
    <!-- Header: Progress & Timer -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm">
        <div class="text-sm text-gray-600">
            Soal No. <span class="font-bold text-xl text-blue-600">{{ $currentQuestionIndex + 1 }}</span> dari
            {{ $totalQuestions }}
        </div>
        <div>
            <span class="text-gray-700 font-medium">Waktu Tersisa: </span>
            <span id="exam-timer" class="font-bold text-lg text-red-600" data-end-time="{{ $endTime }}">--:--</span>
        </div>
    </div>

    @if ($this->currentQuestion)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Question Text -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md min-h-[400px]">
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Pertanyaan</h3>

                <div class="prose max-w-none text-gray-800 question-content text-lg leading-relaxed">
                    {!! $this->currentQuestion->question_text !!}
                </div>
            </div>

            <!-- Right: Options -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Pilihan Jawaban</h3>

                <div class="space-y-4">
                    @php
                        $options = $this->currentQuestion->options;
                        // Ensure options is an array
                        if (is_string($options)) {
                            $decoded = json_decode($options, true);
                            if (is_array($decoded)) {
                                $options = $decoded;
                            }
                        }
                    @endphp

                    @if (is_array($options) && count($options) > 0)
                        @foreach ($options as $code => $text)
                            @php
                                // Handle both formats:
                                // Format 1: {"A": "text", "B": "text"} - $code is key, $text is value
                                // Format 2: [{"kode": "A", "teks": "text"}] - $code is index, $text is array
                                if (is_array($text)) {
                                    $optionCode = $text['kode'] ?? $code;
                                    $optionText = $text['teks'] ?? '';
                                } else {
                                    $optionCode = $code;
                                    $optionText = $text;
                                }
                            @endphp
                            <label
                                class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:bg-blue-50 {{ $currentAnswer === $optionCode ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'border-gray-200' }}">
                                <div class="flex items-center h-5">
                                    <input type="radio" wire:click="saveAnswer('{{ $optionCode }}')"
                                        wire:model="currentAnswer" name="answer" value="{{ $optionCode }}"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-bold text-blue-700 mr-2">{{ $optionCode }}.</span>
                                    <span class="text-gray-700 option-text">{!! $optionText !!}</span>
                                </div>
                            </label>
                        @endforeach
                    @else
                        <p class="text-gray-500 italic">Tidak ada pilihan jawaban tersedia.</p>
                    @endif
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex justify-between pt-4 border-t">
                    <button wire:click="prevQuestion" @if ($currentQuestionIndex === 0) disabled @endif
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        ← Sebelumnya
                    </button>

                    <button wire:click="nextQuestion" @if ($currentQuestionIndex === $totalQuestions - 1) disabled @endif
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        Selanjutnya →
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl text-red-600">Terjadi kesalahan memuat soal.</h3>
            <p class="text-gray-500">Silakan refresh halaman atau hubungi pengawas.</p>
        </div>
    @endif

    <script>
        // Timer functionality
        (function() {
            const timerEl = document.getElementById('exam-timer');
            if (!timerEl) return;

            const endTime = new Date(timerEl.dataset.endTime).getTime();

            function updateTimer() {
                const now = new Date().getTime();
                const remaining = endTime - now;

                if (remaining <= 0) {
                    timerEl.textContent = '00:00';
                    timerEl.classList.add('animate-pulse');
                    // Optionally auto-submit here
                    return;
                }

                const hours = Math.floor(remaining / (1000 * 60 * 60));
                const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                if (hours > 0) {
                    timerEl.textContent =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                } else {
                    timerEl.textContent =
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }

                // Warning colors
                if (remaining < 5 * 60 * 1000) { // Less than 5 minutes
                    timerEl.classList.add('text-red-600', 'animate-pulse');
                } else if (remaining < 10 * 60 * 1000) { // Less than 10 minutes
                    timerEl.classList.add('text-orange-500');
                }
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        })();

        // MathJax re-render on question change
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('question-changed', () => {
                setTimeout(() => {
                    if (window.renderMathJax) {
                        window.renderMathJax();
                    }
                }, 100);
            });
        });
    </script>
</div>
