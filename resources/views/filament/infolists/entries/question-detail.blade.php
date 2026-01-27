<div class="question-detail-content prose prose-sm max-w-none" x-data="{
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

    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-amber-900 mb-1">Soal</h4>
                <div class="text-sm text-gray-700 leading-relaxed question-content">
                    {!! $getState() !!}
                </div>
            </div>
        </div>
    </div>

    <style>
        .question-detail-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .question-detail-content .question-content>p:first-child {
            margin-top: 0;
        }

        .question-detail-content .question-content>p:last-child {
            margin-bottom: 0;
        }

        .question-detail-content a {
            color: #d97706;
            text-decoration: underline;
        }

        .question-detail-content a:hover {
            color: #b45309;
        }
    </style>
</div>
