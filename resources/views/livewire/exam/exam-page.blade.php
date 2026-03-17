@include('livewire.exam.partials.styles')
@include('livewire.exam.partials.scripts')

<div x-data="{
    // Sinkronisasi status 'step' dari Livewire ke Alpine.js
    step: @entangle('step'),

    // Client-side optimistic violation counter (untuk NO DELAY display)
    violationCounter: {{ $violationCount ?? 0 }},

    showConfirmFinishLocal: false,

    logActivity(action, message, severity = 'warning') {
        // Deteksi pelanggaran hanya aktif ketika ujian benar-benar dimulai
        if (this.step !== 'exam') return;

        // Throttle: cegah spam ke DB (2 detik cooldown)
        if (this._lastLog && Date.now() - this._lastLog < 2000) return;
        this._lastLog = Date.now();

        // OPTIMISTIC: Increment counter IMMEDIATELY untuk display tanpa delay
        // Modal akan tampil dengan count yang benar (1, 2, 3, ...) instantly
        this.violationCounter++;

        // OPTIMISTIC: Tampilkan modal pelanggaran INSTAN di client
        // tanpa menunggu respons server
        const messages = {
            'tab_switch': 'Anda berpindah ke tab lain atau meminimalkan jendela ujian.',
            'window_blur': 'Anda mengklik di luar halaman ujian.',
            'copy_attempt': 'Anda mencoba menyalin teks dari soal ujian.',
            'paste_attempt': 'Anda mencoba menempel teks pada halaman ujian.',
            'right_click': 'Anda menggunakan menu klik kanan pada soal ujian.',
            'screenshot_attempt': 'Sistem mendeteksi upaya pengambilan tangkapan layar.',
        };
        const actionLabels = {
            'tab_switch': 'Berpindah tab / meminimalkan browser',
            'window_blur': 'Klik di luar jendela ujian',
            'copy_attempt': 'Percobaan menyalin teks',
            'paste_attempt': 'Percobaan menempel teks',
            'right_click': 'Menu klik kanan',
            'screenshot_attempt': 'Pengambilan screenshot',
        };
        const sources = {
            'tab_switch': 'Halaman ujian tidak lagi menjadi jendela aktif.',
            'window_blur': 'Fokus pengguna keluar dari halaman ujian.',
            'copy_attempt': 'Sistem mendeteksi aksi menyalin dari keyboard/menu.',
            'paste_attempt': 'Sistem mendeteksi aksi menempel dari keyboard/menu.',
            'right_click': 'Menu konteks terdeteksi pada halaman ujian.',
            'screenshot_attempt': 'Sistem mendeteksi tombol/kombinasi tangkapan layar.',
        };
        // Format waktu KONSISTEN dengan server (dd-mm-yyyy HH:mm:ss)
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        const formattedDate = `${day}-${month}-${year} ${hours}:${mins}:${secs}`;

        this.$wire.showViolationModal = true;
        this.$wire.violationMessage = messages[action] || 'Aktivitas mencurigakan terdeteksi.';
        this.$wire.violationAction = actionLabels[action] || 'Aktivitas mencurigakan';
        this.$wire.violationSource = sources[action] || 'event monitoring sistem';
        this.$wire.violationDetectedAt = formattedDate;

        // Catat ke server (violationCount akan di-increment di server)
        this.$wire.logActivity(action, null, severity);
    }
}" x-init="// 1. Detect Tab Switching / Visibility Change
document.addEventListener('visibilitychange', () => {
    if (step !== 'exam') return;
    if (document.hidden) {
        logActivity('tab_switch', '', 'warning');
    }
});

// 2. Detect Window Blur (split screen / klik di luar jendela browser)
window.addEventListener('blur', () => {
    if (step !== 'exam') return;
    logActivity('window_blur', '', 'warning');
});

// 3. Detect Copy/Paste
document.addEventListener('copy', (e) => {
    if (step !== 'exam') return;
    e.preventDefault();
    logActivity('copy_attempt', '', 'warning');
    return false;
});
document.addEventListener('paste', (e) => {
    if (step !== 'exam') return;
    e.preventDefault();
    logActivity('paste_attempt', '', 'warning');
    return false;
});
document.addEventListener('cut', (e) => {
    e.preventDefault();
    return false;
});

// 4. Disable Context Menu
document.addEventListener('contextmenu', (e) => {
    if (step === 'result') return;
    e.preventDefault();
    if (step === 'exam') {
        logActivity('right_click', '', 'warning');
    }
    return false;
});

// 4b. Block shortcut inspect (aktif di rules & exam)
document.addEventListener('keydown', (e) => {
    if (step === 'result') return;

    const key = (e.key || '').toLowerCase();
    const isInspectShortcut =
        key === 'f12' ||
        ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c', 'k'].includes(key)) ||
        ((e.ctrlKey || e.metaKey) && key === 'u');

    if (!isInspectShortcut) return;

    e.preventDefault();

    if (step === 'exam') {
        logActivity('right_click', '', 'warning');
    }

    return false;
});

// 5. Detect Screenshot attempts (PrintScreen, Win+Shift+S, macOS Cmd+Shift+3/4/5)
document.addEventListener('keyup', (e) => {
    if (step !== 'exam') return;
    if (e.key === 'PrintScreen') {
        logActivity('screenshot_attempt', '', 'warning');
    }
});
document.addEventListener('keydown', (e) => {
    if (step !== 'exam') return;
    if (
        e.key === 'PrintScreen' ||
        (e.metaKey && e.shiftKey && e.key.toLowerCase() === 's') ||
        (e.metaKey && e.shiftKey && ['3', '4', '5'].includes(e.key))
    ) {
        e.preventDefault();
        logActivity('screenshot_attempt', '', 'warning');
    }
});" @effect // Sync local violationCounter dengan
        $wire.violationCount dari server // Ini memastikan counter tetap akurat setelah server confirm increment
    this.violationCounter=$wire.violationCount; @endeffect>

    {{-- Violation Modal (Alpine.js controls visibility via x-show) --}}
    @include('livewire.exam.components.violation-modal')

    <span wire:poll.keep-alive.5s="monitorSessionStatus" style="display: none;" id="exam-session-status"></span>

    @if ($step === 'rules')
        @include('livewire.exam.steps.rules')
    @elseif($step === 'exam')
        @include('livewire.exam.components.confirm-finish-modal')
        @include('livewire.exam.components.overlay-result-modal')
        @include('livewire.exam.steps.exam')
    @elseif($step === 'result')
        @include('livewire.exam.steps.result')
    @endif

</div>
