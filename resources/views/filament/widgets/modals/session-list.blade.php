<ul class="divide-y divide-gray-200 dark:divide-gray-700">
    @forelse($records as $session)
        @php $user = $session->user; $package = $session->examPackage; @endphp
        <li class="py-3 flex justify-between items-center">
            <div>
                <p class="font-medium text-sm text-danger-700 dark:text-danger-400">
                    {{ $user ? $user->name : 'N/A' }}
                </p>
                <p class="text-xs text-gray-500 mb-1">
                    {{ $session->updated_at ? $session->updated_at->format('d M H:i') : '' }}
                </p>
                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    {{ $package ? $package->title : 'N/A' }}
                </p>
            </div>
            
            <div>
                <a href="{{ $actionUrl($session) }}" 
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Detail
                </a>
            </div>
        </li>
    @empty
        <li class="py-4 text-center text-sm text-gray-500">Tidak ada data.</li>
    @endforelse
</ul>