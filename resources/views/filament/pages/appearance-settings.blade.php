<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Choose Dashboard Theme Color') }}</x-slot>
        <x-slot name="description">
            {{ __('Click a color to directly apply the theme.') }}
        </x-slot>

        @php
            $colors = [
                'yellow' => ['label' => __('Default'), 'hex' => '#f59e0b', 'ring' => 'ring-amber-500'],
                'orange' => ['label' => __('Orange'), 'hex' => '#f97316', 'ring' => 'ring-orange-500'],
                'red' => ['label' => __('Red'), 'hex' => '#f43f5e', 'ring' => 'ring-rose-500'],
                'pink' => ['label' => __('Pink'), 'hex' => '#ec4899', 'ring' => 'ring-pink-500'],
                'purple' => ['label' => __('Purple'), 'hex' => '#a855f7', 'ring' => 'ring-purple-500'],
                'indigo' => ['label' => __('Dark Blue'), 'hex' => '#6366f1', 'ring' => 'ring-indigo-500'],
                'sky' => ['label' => __('Sky Blue'), 'hex' => '#0ea5e9', 'ring' => 'ring-sky-500'],
                'cyan' => ['label' => __('Light Blue'), 'hex' => '#06b6d4', 'ring' => 'ring-cyan-500'],
                'teal' => ['label' => __('Teal'), 'hex' => '#14b8a6', 'ring' => 'ring-teal-500'],
                'green' => ['label' => __('Green'), 'hex' => '#10b981', 'ring' => 'ring-emerald-500'],
                'lime' => ['label' => __('Lime'), 'hex' => '#84cc16', 'ring' => 'ring-lime-500'],
            ];
        @endphp

        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($colors as $key => $color)
                <button type="button" wire:click="setColor('{{ $key }}')"
                    class="group flex flex-col items-center gap-2 p-3 rounded-xl
                           hover:bg-gray-50 dark:hover:bg-white/5
                           transition-colors duration-150"
                    title="{{ $color['label'] }}">
                    <span
                        class="relative w-12 h-12 rounded-full shadow-md transition-transform duration-200
                               group-hover:scale-110
                               {{ $currentColor === $key ? 'ring-[3px] ring-offset-2 ' . $color['ring'] . ' scale-110' : '' }}"
                        style="background-color: {{ $color['hex'] }}">
                        @if ($currentColor === $key)
                            <span class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        @endif
                    </span>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                        {{ $color['label'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </x-filament::section>

    {{-- ── Language Section ─────────────────────────────────────────── --}}
    <x-filament::section>
        <x-slot name="heading">{{ __('Interface Language') }}</x-slot>
        <x-slot name="description">
            {{ __('Choose your preferred display language for the admin panel.') }}
        </x-slot>

        @php $currentLocale = app()->getLocale(); @endphp

        <div class="flex flex-wrap gap-4">

            {{-- Indonesia --}}
            <a href="{{ url('/lang/id') }}"
                class="group flex items-center gap-3 px-5 py-4 rounded-xl border-2 transition-all duration-150
                       {{ $currentLocale === 'id'
                           ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md'
                           : 'border-gray-200 dark:border-white/10 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-white/5' }}"
                style="min-width:160px;text-decoration:none">
                <svg width="28" height="20" viewBox="0 0 20 14" class="rounded shadow-sm shrink-0"
                    xmlns="http://www.w3.org/2000/svg">
                    <rect width="20" height="7" fill="#CE1126" />
                    <rect y="7" width="20" height="7" fill="#FFFFFF" />
                </svg>
                <div>
                    <p
                        class="font-semibold text-sm
                              {{ $currentLocale === 'id' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200' }}">
                        Indonesia</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Bahasa Indonesia</p>
                </div>
                @if ($currentLocale === 'id')
                    <svg class="ml-auto w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </a>

            {{-- English --}}
            <a href="{{ url('/lang/en') }}"
                class="group flex items-center gap-3 px-5 py-4 rounded-xl border-2 transition-all duration-150
                       {{ $currentLocale === 'en'
                           ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md'
                           : 'border-gray-200 dark:border-white/10 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-white/5' }}"
                style="min-width:160px;text-decoration:none">
                <svg width="28" height="20" viewBox="0 0 20 14" class="rounded shadow-sm shrink-0"
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
                <div>
                    <p
                        class="font-semibold text-sm
                              {{ $currentLocale === 'en' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200' }}">
                        English</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">English (US)</p>
                </div>
                @if ($currentLocale === 'en')
                    <svg class="ml-auto w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </a>

        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-panels::page>
