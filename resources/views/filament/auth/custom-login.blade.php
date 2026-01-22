<x-filament-panels::page.simple>
    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-amber-50 to-amber-100">
        <style>
            /* Hide default simple header (logo/heading) from Filament */
            .fi-simple-header {
                display: none !important;
            }

            /* Neutralize default simple layout width on this page */
            .fi-simple-main.fi-width-lg {
                max-width: none !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                display: block !important;
            }

            .fi-simple-main.fi-width-lg>* {
                max-width: none !important;
                width: 100% !important;
            }
        </style>

        <div
            class="max-w-5xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden ring-1 ring-amber-900/10 flex flex-col-reverse md:flex-row">

            {{-- Left (mobile bottom): Login Form --}}
            <div class="w-full md:w-1/2 p-8 sm:p-12">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-amber-600 text-white p-2 rounded-lg shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="font-bold text-xl text-amber-900 tracking-wide">BAPETEN CAT</span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Login Administrator</h2>
                    <p class="mt-2 text-slate-600 text-sm">Akses panel pengelolaan CAT dengan kredensial Anda.</p>
                </div>

                <div class="space-y-5">
                    {{ $this->content }}
                </div>

                <p class="mt-6 text-xs text-slate-400">&copy; {{ date('Y') }} BAPETEN — Computer Assisted Test
                    System.</p>
            </div>

            {{-- Right (mobile top): Brand / Guidance --}}
            <div
                class="w-full md:w-1/2 bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 text-white p-8 sm:p-12 relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-64 h-64 bg-amber-300 rounded-full mix-blend-multiply filter blur-3xl opacity-40">
                </div>
                <div
                    class="absolute -top-24 -right-24 w-64 h-64 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-40">
                </div>

                <div class="relative space-y-6">
                    <div
                        class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/20 text-white shadow-lg ring-4 ring-white/15">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c1.38 0 2.5-1.12 2.5-2.5S13.38 3 12 3 9.5 4.12 9.5 5.5 10.62 8 12 8zm0 0c2.5 0 4.5 2 4.5 4.5S14.5 17 12 17s-4.5-2-4.5-4.5S9.5 8 12 8zm0 0v9" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-semibold tracking-tight">Integritas & Keamanan Ujian</h3>
                    <p class="text-amber-50/90 leading-relaxed text-sm">
                        Pastikan Anda menggunakan kredensial resmi. Aktivitas login diawasi dan dicatat untuk menjaga
                        integritas ujian CAT.
                    </p>

                    <div class="bg-white/10 border border-white/15 rounded-xl p-4 backdrop-blur-sm shadow-lg">
                        <p class="text-sm text-amber-50 leading-relaxed flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            </svg>
                            Session login dibatasi waktu. Gunakan jaringan yang aman dan hindari berbagi kredensial.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page.simple>
