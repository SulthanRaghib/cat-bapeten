@include('livewire.exam.partials.styles')
@include('livewire.exam.partials.scripts')

<div x-data="{
    // Sinkronisasi status 'step' dari Livewire ke Alpine.js
    step: @entangle('step'),

    logActivity(action, message, severity = 'warning') {
        // Blokir jika sudah di halaman result
        if (this.step === 'result') return;

        // Throttle: cegah spam ke DB (2 detik cooldown)
        if (this._lastLog && Date.now() - this._lastLog < 2000) return;
        this._lastLog = Date.now();

        // OPTIMISTIC: Tampilkan modal pelanggaran INSTAN di client
        // tanpa menunggu respons server
        const messages = {
            'tab_switch': 'Peserta berpindah tab atau meminimalkan browser.',
            'window_blur': 'Peserta mengklik di luar jendela ujian.',
            'copy_attempt': 'Percobaan menyalin teks soal (Copy).',
            'paste_attempt': 'Percobaan menempel teks (Paste).',
            'right_click': 'Percobaan klik kanan (Context Menu).',
            'screenshot_attempt': 'Percobaan tangkapan layar (Screenshot).',
        };
        this.$wire.showViolationModal = true;
        this.$wire.violationMessage = messages[action] || 'Aktivitas mencurigakan terdeteksi.';

        // Catat ke server (violationCount diperbarui dari respons server)
        this.$wire.logActivity(action, null, severity);
    }
}" x-init="
    // 1. Detect Tab Switching / Visibility Change
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            logActivity('tab_switch', '', 'warning');
        }
    });

    // 2. Detect Window Blur (split screen / klik di luar jendela browser)
    window.addEventListener('blur', () => {
        logActivity('window_blur', '', 'warning');
    });

    // 3. Detect Copy/Paste
    document.addEventListener('copy', (e) => {
        e.preventDefault();
        logActivity('copy_attempt', '', 'warning');
        return false;
    });
    document.addEventListener('paste', (e) => {
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
        e.preventDefault();
        logActivity('right_click', '', 'warning');
        return false;
    });

    // 5. Detect Screenshot attempts (PrintScreen, Win+Shift+S, macOS Cmd+Shift+3/4/5)
    document.addEventListener('keyup', (e) => {
        if (e.key === 'PrintScreen') {
            logActivity('screenshot_attempt', '', 'warning');
        }
    });
    document.addEventListener('keydown', (e) => {
        if (
            e.key === 'PrintScreen' ||
            (e.metaKey && e.shiftKey && e.key.toLowerCase() === 's') ||
            (e.metaKey && e.shiftKey && ['3', '4', '5'].includes(e.key))
        ) {
            e.preventDefault();
            logActivity('screenshot_attempt', '', 'warning');
        }
    });
">

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
