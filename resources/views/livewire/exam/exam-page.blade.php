@include('livewire.exam.partials.styles')
@include('livewire.exam.partials.scripts')

<div x-data="{
    // Sinkronisasi status 'step' dari Livewire ke Alpine.js
    step: @entangle('step'),

    // Initial state: Ambil MAX(server, global) untuk mencegah reset saat re-render
    violationCounter: 0,
    showConfirmFinishLocal: false,

    init() {
        // 1. Sinkronisasi State (Anti-Flicker Logic)
        let serverCount = {{ $violationCount ?? 0 }};
        let globalCount = window.globalViolationCounter || 0;
        
        // Selalu ambil nilai tertinggi => Ratchet Mechanism
        this.violationCounter = Math.max(serverCount, globalCount);
        window.globalViolationCounter = this.violationCounter;

        // 2. Watch variable server
        // Hanya update jika server mengirim nilai yang LEBIH BESAR
        this.$watch('$wire.violationCount', (newVal) => {
            if (newVal > this.violationCounter) {
                this.violationCounter = newVal;
                window.globalViolationCounter = newVal;
            }
        });

        // 3. Event Listeners untuk Monitoring
        // Menggunakan arrow functions agar 'this' tetap mengacu pada komponen Alpine

        // Tab Switching
        document.addEventListener('visibilitychange', () => {
            if (this.step !== 'exam' || !document.hidden) return;
            this.logActivity('tab_switch', '', 'warning');
        });

        // Window Blur
        window.addEventListener('blur', () => {
            if (this.step !== 'exam') return;
            this.logActivity('window_blur', '', 'warning');
        });

        // Copy/Paste Blocking
        const preventAndLog = (e, type) => {
            if (this.step !== 'exam') return;
            e.preventDefault();
            this.logActivity(type + '_attempt', '', 'warning');
            return false;
        };
        document.addEventListener('copy', (e) => preventAndLog(e, 'copy'));
        document.addEventListener('paste', (e) => preventAndLog(e, 'paste'));
        document.addEventListener('cut', (e) => { e.preventDefault(); return false; });

        // Context Menu & Inspect
        document.addEventListener('contextmenu', (e) => {
            if (this.step === 'result') return;
            e.preventDefault();
            if (this.step === 'exam') this.logActivity('right_click', '', 'warning');
            return false;
        });

        document.addEventListener('keydown', (e) => {
            if (this.step === 'result') return;
            const key = (e.key || '').toLowerCase();
            const isInspect = key === 'f12' || 
                ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c', 'k'].includes(key)) || 
                ((e.ctrlKey || e.metaKey) && key === 'u');
            
            if (isInspect) {
                e.preventDefault();
                if (this.step === 'exam') this.logActivity('right_click', '', 'warning');
                return false;
            }

            // Screenshot Detection (PrintScreen / Shortcuts)
            if (this.step === 'exam') {
                 if (e.key === 'PrintScreen' || 
                    (e.metaKey && e.shiftKey && (e.key.toLowerCase() === 's' || ['3', '4', '5'].includes(e.key)))) {
                    e.preventDefault();
                    this.logActivity('screenshot_attempt', '', 'warning');
                }
            }
        });

        document.addEventListener('keyup', (e) => {
            if (this.step !== 'exam') return;
            if (e.key === 'PrintScreen') this.logActivity('screenshot_attempt', '', 'warning');
        });
    },

    logActivity(action, message, severity = 'warning') {
        if (this.step !== 'exam') return;

        // Throttle
        if (this._lastLog && Date.now() - this._lastLog < 2000) return;
        this._lastLog = Date.now();

        // OPTIMISTIC UPDATE: Increment local & global
        this.violationCounter++;
        window.globalViolationCounter = this.violationCounter;

        // Setup pesan modal
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

        const now = new Date();
        const formattedDate = `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;

        this.$wire.showViolationModal = true;
        this.$wire.violationMessage = messages[action] || 'Aktivitas mencurigakan terdeteksi.';
        this.$wire.violationAction = actionLabels[action] || 'Aktivitas mencurigakan';
        this.$wire.violationSource = sources[action] || 'event monitoring sistem';
        this.$wire.violationDetectedAt = formattedDate;

        this.$wire.logActivity(action, null, severity);
    }
}">

    {{-- Violation Modal (Alpine.js controls visibility via x-show) --}}
    @include('livewire.exam.components.violation-modal')

    <span wire:poll.keep-alive.5s="monitorSessionStatus" style="display: none;" id="exam-session-status"></span>

    @if ($step === 'rules')
        @include('livewire.exam.steps.rules')
    @elseif($step === 'exam')
        @include('livewire.exam.components.confirm-finish-modal')
        @include('livewire.exam.steps.exam')
    @elseif($step === 'result')
        @include('livewire.exam.steps.result')
    @endif

</div>
