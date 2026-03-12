<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Pilih Warna Tema Dashboard</x-slot>
        <x-slot name="description">
            Klik warna untuk langsung menerapkan tema.
        </x-slot>

        @php
            $colors = [
                'yellow' => ['label' => 'Default',      'hex' => '#f59e0b', 'ring' => 'ring-amber-500'],
                'orange' => ['label' => 'Oranye',      'hex' => '#f97316', 'ring' => 'ring-orange-500'],
                'red'    => ['label' => 'Merah',       'hex' => '#f43f5e', 'ring' => 'ring-rose-500'],
                'pink'   => ['label' => 'Pink',        'hex' => '#ec4899', 'ring' => 'ring-pink-500'],
                'purple' => ['label' => 'Ungu',        'hex' => '#a855f7', 'ring' => 'ring-purple-500'],
                'indigo' => ['label' => 'Biru Tua',    'hex' => '#6366f1', 'ring' => 'ring-indigo-500'],
                'sky'    => ['label' => 'Biru Langit', 'hex' => '#0ea5e9', 'ring' => 'ring-sky-500'],
                'cyan'   => ['label' => 'Biru Muda',   'hex' => '#06b6d4', 'ring' => 'ring-cyan-500'],
                'teal'   => ['label' => 'Tosca',       'hex' => '#14b8a6', 'ring' => 'ring-teal-500'],
                'green'  => ['label' => 'Hijau',       'hex' => '#10b981', 'ring' => 'ring-emerald-500'],
                'lime'   => ['label' => 'Lime',        'hex' => '#84cc16', 'ring' => 'ring-lime-500'],
            ];
        @endphp

        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($colors as $key => $color)
                <button
                    type="button"
                    wire:click="setColor('{{ $key }}')"
                    class="group flex flex-col items-center gap-2 p-3 rounded-xl
                           hover:bg-gray-50 dark:hover:bg-white/5
                           transition-colors duration-150"
                    title="{{ $color['label'] }}"
                >
                    <span
                        class="relative w-12 h-12 rounded-full shadow-md transition-transform duration-200
                               group-hover:scale-110
                               {{ $currentColor === $key ? 'ring-[3px] ring-offset-2 ' . $color['ring'] . ' scale-110' : '' }}"
                        style="background-color: {{ $color['hex'] }}"
                    >
                        @if ($currentColor === $key)
                            <span class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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

    <x-filament-actions::modals />
</x-filament-panels::page>
