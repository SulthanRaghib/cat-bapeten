<ul class="divide-y divide-gray-200 dark:divide-gray-700">
    @forelse($records as $record)
        <li class="py-3 flex justify-between items-center">
            <div>
                <p class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $record->title }}</p>
                <p class="text-xs text-gray-500">
                    {{ $record->start_time ? $record->start_time->format('d M Y H:i') : '-' }} -
                    {{ $record->end_time ? $record->end_time->format('H:i') : '-' }}
                </p>
            </div>
            
            @if(isset($actionUrl))
                <a href="{{ $actionUrl($record) }}" 
                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-900/50 dark:text-primary-400 dark:hover:bg-primary-900">
                    {{ $actionLabel ?? 'Lihat' }}
                </a>
            @endif
        </li>
    @empty
        <li class="py-4 text-center text-sm text-gray-500">Tidak ada data.</li>
    @endforelse
</ul>