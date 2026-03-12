@php
    $locale = app()->getLocale();
    $isId = $locale === 'id';
    $isEn = $locale === 'en';
@endphp

<div x-data="{ open: false }" x-on:click.outside="open = false" class="relative flex items-center mr-3">

    {{-- ═══ Trigger button ═══ --}}
    <button x-on:click="open = !open" type="button"
        class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold
                   bg-white dark:bg-white/10
                   border border-gray-200/80 dark:border-white/10
                   text-gray-700 dark:text-gray-100
                   shadow-sm hover:shadow-md hover:shadow-amber-100/60 dark:hover:shadow-amber-900/20
                   hover:border-amber-400 dark:hover:border-amber-500
                   transition-all duration-200 ease-out
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1">

        @if ($isId)
            {{-- Indonesia flag (red/white bicolor) --}}
            <svg width="20" height="14" viewBox="0 0 20 14" class="rounded-[3px] shadow-sm shrink-0"
                xmlns="http://www.w3.org/2000/svg">
                <rect width="20" height="7" fill="#CE1126" />
                <rect y="7" width="20" height="7" fill="#FFFFFF" />
            </svg>
            <span>Indonesia</span>
        @else
            {{-- US flag (stripes + blue canton) --}}
            <svg width="20" height="14" viewBox="0 0 20 14" class="rounded-[3px] shadow-sm shrink-0"
                xmlns="http://www.w3.org/2000/svg">
                <rect width="20" height="14" fill="#FFFFFF" />
                <rect width="20" height="1.08" y="0" fill="#B22234" />
                <rect width="20" height="1.08" y="2.15" fill="#B22234" />
                <rect width="20" height="1.08" y="4.31" fill="#B22234" />
                <rect width="20" height="1.08" y="6.46" fill="#B22234" />
                <rect width="20" height="1.08" y="8.62" fill="#B22234" />
                <rect width="20" height="1.08" y="10.77" fill="#B22234" />
                <rect width="20" height="1.08" y="12.92" fill="#B22234" />
                <rect width="8" height="7.5" fill="#3C3B6E" />
            </svg>
            <span>English</span>
        @endif

        {{-- Chevron --}}
        <svg x-bind:class="{ 'rotate-180': open }"
            class="w-3 h-3 text-gray-400 group-hover:text-amber-500 transition-transform duration-200 shrink-0"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>

    {{-- ═══ Dropdown panel ═══ --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1" style="display: none"
        class="absolute right-0 top-full mt-2 z-50 w-44
                rounded-xl overflow-hidden
                bg-white dark:bg-gray-800
                border border-gray-100 dark:border-white/10
                shadow-xl shadow-black/10 dark:shadow-black/50
                ring-1 ring-black/5 dark:ring-white/5">

        {{-- ── Indonesia option ── --}}
        <a href="{{ route('lang.switch', 'id') }}"
            class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150
                  {{ $isId
                      ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'
                      : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5' }}">
            <svg width="20" height="14" viewBox="0 0 20 14" class="rounded-[3px] shadow-sm shrink-0"
                xmlns="http://www.w3.org/2000/svg">
                <rect width="20" height="7" fill="#CE1126" />
                <rect y="7" width="20" height="7" fill="#FFFFFF" />
            </svg>
            <span class="{{ $isId ? 'font-semibold' : 'font-medium' }}">Indonesia</span>
            @if ($isId)
                <svg class="ml-auto w-4 h-4 text-amber-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            @endif
        </a>

        <div class="h-px bg-gray-100 dark:bg-white/10 mx-3"></div>

        {{-- ── English option ── --}}
        <a href="{{ route('lang.switch', 'en') }}"
            class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150
                  {{ $isEn
                      ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'
                      : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5' }}">
            <svg width="20" height="14" viewBox="0 0 20 14" class="rounded-[3px] shadow-sm shrink-0"
                xmlns="http://www.w3.org/2000/svg">
                <rect width="20" height="14" fill="#FFFFFF" />
                <rect width="20" height="1.08" y="0" fill="#B22234" />
                <rect width="20" height="1.08" y="2.15" fill="#B22234" />
                <rect width="20" height="1.08" y="4.31" fill="#B22234" />
                <rect width="20" height="1.08" y="6.46" fill="#B22234" />
                <rect width="20" height="1.08" y="8.62" fill="#B22234" />
                <rect width="20" height="1.08" y="10.77" fill="#B22234" />
                <rect width="20" height="1.08" y="12.92" fill="#B22234" />
                <rect width="8" height="7.5" fill="#3C3B6E" />
            </svg>
            <span class="{{ $isEn ? 'font-semibold' : 'font-medium' }}">English</span>
            @if ($isEn)
                <svg class="ml-auto w-4 h-4 text-amber-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            @endif
        </a>

    </div>
</div>
