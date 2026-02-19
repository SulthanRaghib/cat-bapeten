@php
    $examAnswer = $getRecord();
    $question = $examAnswer->question;

    // Handle JSON options safely
    $optionsRaw = $question->options;
    if (is_string($optionsRaw)) {
        $options = json_decode($optionsRaw, true);
    } else {
        $options = $optionsRaw;
    }
    $options = is_array($options) ? $options : [];

    $userAnswer = $examAnswer->answer;
    $isUnanswered = $userAnswer === null || $userAnswer === '';

    // Determine Exam Type
    $examPackage = $examAnswer->examSession->examPackage ?? null;
    $examType = $examPackage->type ?? 'technical';
@endphp

<div
    class="p-6 rounded-xl border bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md {{ $isUnanswered ? 'border-yellow-300 dark:border-yellow-700' : 'border-gray-200 dark:border-gray-700' }}">

    {{-- Header: Question Text --}}
    <div class="mb-6">
        <div class="flex items-start justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pertanyaan</span>

            @if ($isUnanswered)
                {{-- Not answered --}}
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Tidak Dijawab
                </span>
            @elseif ($examType === 'structural')
                {{-- Structural: Show Points Only --}}
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Poin: {{ $examAnswer->score }}
                </span>
            @else
                {{-- Technical: Show Correct/Incorrect w/ Points --}}
                @if ($examAnswer->score > 0)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Benar (+{{ $examAnswer->score }} Poin)
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Salah (0 Poin)
                    </span>
                @endif
            @endif
        </div>
        <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 text-base leading-relaxed">
            {!! $question->question_text !!}
        </div>
    </div>

    {{-- Unanswered Notice --}}
    @if ($isUnanswered)
        <div
            class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
            <x-filament::icon icon="heroicon-m-exclamation-triangle"
                class="h-4 w-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0" />
            <span class="text-sm text-yellow-800 dark:text-yellow-300 font-medium">Peserta tidak memberikan jawaban pada
                soal ini.</span>
        </div>
    @endif

    {{-- Options List --}}
    <div class="space-y-3 {{ $isUnanswered ? 'opacity-75' : '' }}">
        @foreach ($options as $index => $option)
            @php
                // When unanswered, no option is selected
                $isUserSelected = !$isUnanswered && (string) $index === (string) $userAnswer;
                $isCorrectOption = $option['is_correct'] ?? false;

                // Defaults
                $borderColor = 'border-gray-200 dark:border-gray-700';
                $bgColor = 'bg-gray-50 dark:bg-gray-900/50';
                $icon = null;
                $statusText = null;
                $statusTextColor = 'text-gray-500';

                if ($isUnanswered) {
                    // UNANSWERED: only highlight correct answer for technical
                    if ($examType !== 'structural' && $isCorrectOption) {
                        $borderColor = 'border-green-400 ring-1 ring-green-400';
                        $bgColor = 'bg-green-50 dark:bg-green-900/20';
                        $icon = 'heroicon-m-check-circle';
                        $statusTextColor = 'text-green-600 dark:text-green-400';
                        $statusText = 'Jawaban Benar';
                    }
                } elseif ($examType === 'structural') {
                    // STRUCTURAL LOGIC
                    if ($isUserSelected) {
                        $borderColor = 'border-blue-500 ring-1 ring-blue-500';
                        $bgColor = 'bg-blue-50 dark:bg-blue-900/20';
                        $icon = 'heroicon-m-check-circle';
                        $statusTextColor = 'text-blue-600 dark:text-blue-400';
                        $statusText = 'Jawaban Anda';
                    }
                } else {
                    // TECHNICAL LOGIC
                    if ($isUserSelected && $isCorrectOption) {
                        $borderColor = 'border-green-500 ring-1 ring-green-500';
                        $bgColor = 'bg-green-50 dark:bg-green-900/20';
                        $icon = 'heroicon-m-check-circle';
                        $statusTextColor = 'text-green-600 dark:text-green-400';
                        $statusText = 'Jawaban Benar';
                    } elseif ($isUserSelected && !$isCorrectOption) {
                        $borderColor = 'border-red-500 ring-1 ring-red-500';
                        $bgColor = 'bg-red-50 dark:bg-red-900/20';
                        $icon = 'heroicon-m-x-circle';
                        $statusTextColor = 'text-red-600 dark:text-red-400';
                        $statusText = 'Jawaban Anda (Salah)';
                    } elseif ($isCorrectOption) {
                        $borderColor = 'border-green-500 ring-1 ring-green-500';
                        $bgColor = 'bg-green-50 dark:bg-green-900/20';
                        $icon = 'heroicon-m-check-circle';
                        $statusTextColor = 'text-green-600 dark:text-green-400';
                        $statusText = 'Jawaban Benar';
                    }
                }
            @endphp

            <div
                class="relative flex items-start p-4 rounded-lg border {{ $borderColor }} {{ $bgColor }} transition-colors">
                <div class="flex items-center h-5">
                    <div
                        class="flex items-center justify-center h-5 w-5 rounded-full border {{ $isUserSelected ? ($examType === 'structural' ? 'border-primary-600 bg-primary-600' : 'border-primary-600 bg-primary-600') : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800' }}">
                        @if ($isUserSelected)
                            <div class="h-2.5 w-2.5 rounded-full bg-white"></div>
                        @else
                            <span class="text-xs text-gray-500 font-medium">{{ chr(65 + $index) }}</span>
                        @endif
                    </div>
                </div>
                <div class="ml-3 flex-1 text-sm">
                    <label class="font-medium text-gray-900 dark:text-gray-100 block">
                        {!! $option['answer_text'] !!}
                    </label>
                    @if ($statusText)
                        <p class="{{ $statusTextColor }} text-xs mt-1 font-semibold">{{ $statusText }}</p>
                    @endif
                </div>
                @if ($icon)
                    <div class="ml-3 flex-shrink-0">
                        <x-filament::icon :icon="$icon"
                            class="h-5 w-5 {{ $examType === 'structural' ? 'text-blue-500' : ($isCorrectOption ? 'text-green-500' : 'text-red-500') }}" />
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Discussion / Explanation Section --}}
    @if (!empty($question->explanation))
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-2 text-primary-600 dark:text-primary-400">
                <x-filament::icon icon="heroicon-m-light-bulb" class="h-5 w-5" />
                <span class="font-bold text-sm uppercase">Pembahasan</span>
            </div>
            <div
                class="prose dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/10 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
                {!! $getRecord()->question->explanation !!}
            </div>
        </div>
    @endif
</div>
