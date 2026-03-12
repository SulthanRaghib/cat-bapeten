<div
    x-data="{
        open: false,
        current: @js($currentColor),
        hexMap: @js(collect($colors)->mapWithKeys(fn ($c, $key) => [$key => $c['hex']])),
        palettes: @js(collect($colors)->mapWithKeys(fn ($c, $key) => [$key => \App\Livewire\ColorThemeSwitcher::getOklchPalette($key)])),

        applyPalette(color) {
            const palette = this.palettes[color];
            if (!palette) return;
            const root = document.documentElement;
            Object.keys(palette).forEach(shade => {
                root.style.setProperty('--primary-' + shade, palette[shade]);
            });
        }
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative flex items-center"
>
    {{-- ── Trigger button ─────────────────────────────── --}}
    <button
        x-on:click="open = !open"
        type="button"
        title="Ganti Tema Warna"
        class="relative flex items-center justify-center w-9 h-9 rounded-lg
               hover:bg-gray-100 dark:hover:bg-white/5
               focus:outline-none
               transition-colors duration-200"
    >
        {{-- Paint palette icon — tinted with active color --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 transition-colors duration-300"
             viewBox="0 0 24 24" fill="currentColor"
             x-bind:style="'color:' + hexMap[current]">
            <path d="M12 3C7.03 3 3 7.03 3 12c0 4.97 4.03 9 9 9 .83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-9-9-9zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
        </svg>
    </button>

    {{-- ── Dropdown panel ──────────────────────────────── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        x-cloak
        style="position:fixed; top:56px; right:12px; z-index:9999;"
        class="w-72 rounded-2xl
               bg-white dark:bg-gray-800
               shadow-xl shadow-black/10 dark:shadow-black/40
               ring-1 ring-black/5 dark:ring-white/10
               p-4"
    >
        {{-- Header --}}
        <div class="flex items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-500" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75c0 1.961-.576 3.786-1.57 5.316A6.749 6.749 0 0 1 12 20.25H9.75A2.25 2.25 0 0 1 7.5 18v-.26a3 3 0 0 0-1.96-2.814l-.143-.053A1.5 1.5 0 0 1 4.5 13.5v-.316c0-.71.265-1.388.736-1.904A9.753 9.753 0 0 0 2.25 12Zm7.5-3a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm4.5 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm2.25 5.25a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/>
            </svg>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Tema Warna
            </span>
        </div>

        {{-- Color grid: 4 cols, circle + label --}}
        <div class="grid grid-cols-4 gap-1">
            @foreach ($colors as $key => $color)
                <button
                    type="button"
                    wire:click="setColor('{{ $key }}')"
                    x-on:click="
                        current = '{{ $key }}';
                        applyPalette('{{ $key }}');
                        open = false;
                    "
                    title="{{ $color['label'] }}"
                    class="group relative flex flex-col items-center gap-1.5 pt-2 pb-1.5 px-1 rounded-xl
                           hover:bg-gray-50 dark:hover:bg-white/5
                           transition-colors duration-150"
                >
                    {{-- Circle --}}
                    <span
                        class="w-9 h-9 rounded-full flex-shrink-0 transition-transform duration-200 ease-out group-hover:scale-110"
                        style="background-color:{{ $color['hex'] }}"
                        x-bind:class="current === '{{ $key }}'
                            ? 'scale-110 ring-[3px] ring-offset-2 ring-offset-white dark:ring-offset-gray-800'
                            : 'ring-1 ring-black/10 dark:ring-white/10'"
                        x-bind:style="current === '{{ $key }}'
                            ? 'background-color:{{ $color['hex'] }};outline:none;box-shadow:0 0 0 3px {{ $color['hex'] }}55'
                            : 'background-color:{{ $color['hex'] }}'"
                    ></span>

                    {{-- Label --}}
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 text-center leading-tight">
                        {{ $color['label'] }}
                    </span>

                    {{-- Active checkmark overlay --}}
                    <div
                        x-show="current === '{{ $key }}'"
                        x-cloak
                        class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-white dark:bg-gray-800
                               flex items-center justify-center shadow ring-1 ring-black/10"
                    >
                        <svg class="w-2.5 h-2.5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>
