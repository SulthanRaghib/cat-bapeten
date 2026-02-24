@include('livewire.exam.partials.styles')
@include('livewire.exam.partials.scripts')

<div x-data="{
    logActivity(action, message, severity = 'warning') {
        // Simple throttle to prevent spamming DB
        if (this._lastLog && Date.now() - this._lastLog < 2000) return;
        this._lastLog = Date.now();

        @this.call('logActivity', action, null, severity);
    }
}" x-init="
    // 1. Detect Tab Switching / Visibility Change
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            logActivity('tab_switch', '', 'warning');
            // alert('Peringatan: Dilarang beralih tab selama ujian berlangsung!');
        }
    });

    // 2. Detect Window Blur (Clicking outside browser)
    // window.addEventListener('blur', () => {
    // Optional: strict mode, might trigger on some popups
    // logActivity('window_blur', '', 'info');
    // });

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
">

    {{-- Violation Modal --}}
    @if ($showViolationModal)
        @include('livewire.exam.components.violation-modal')
    @endif

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
