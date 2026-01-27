@php
    // Fix image URLs to match current server
    if (!function_exists('fixImageUrlsTable')) {
        function fixImageUrlsTable($html)
        {
            if (empty($html)) {
                return '';
            }
            $baseUrl = request()->getSchemeAndHttpHost();
            return preg_replace_callback(
                '/src=["\']https?:\/\/[^\/]+\/storage\/([^"\']+)["\']/i',
                fn($m) => 'src="' . $baseUrl . '/storage/' . $m[1] . '"',
                $html,
            );
        }
    }
    $fixedContent = fixImageUrlsTable($content ?? '');
@endphp
<div class="math-content" x-data="{
    renderMath() {
        if (window.MathJax && window.MathJax.typesetPromise) {
            this.$nextTick(() => {
                setTimeout(() => {
                    MathJax.typesetPromise([this.$el]).catch((err) => console.error('MathJax render error:', err));
                }, 200);
            });
        }
    }
}" x-init="renderMath()" @livewire:navigated.window="renderMath()">
    {!! $fixedContent !!}
</div>

<style>
    .math-content img {
        max-width: 150px;
        max-height: 100px;
        object-fit: contain;
        border-radius: 6px;
        display: inline-block;
        vertical-align: middle;
    }
</style>
