{{-- ══════════════════════════════════════════════════════
     PANEL KOMPAK (selalu tampil di atas editor teks)
     ══════════════════════════════════════════════════════ --}}
<div class="medt-panel">

    {{-- Judul & deskripsi --}}
    <div class="medt-panel-header">
        <div class="medt-panel-icon" aria-hidden="true">∑</div>
        <div>
            <p class="medt-panel-title">{{ __('Math Formula Helper') }}</p>
            <p class="medt-panel-desc">
                {{ __('Click a formula below to copy it instantly, or open the Full Editor to build your own.') }}
            </p>
        </div>
    </div>

    {{-- Chip cepat — menampilkan render KaTeX asli --}}
    <div class="medt-chip-row">
        <template x-for="chip in quickChips" :key="chip.id">
            <button type="button" class="medt-chip" :title="chip.name" @click.prevent="quickCopy(chip)">
                <span class="medt-chip-math" x-html="kr(chip.latex, false)"></span>
                <span class="medt-chip-name" x-text="chip.name"></span>
            </button>
        </template>

        {{-- Tombol buka editor lengkap --}}
        <button type="button" class="medt-open-btn" @click.prevent="openEditor()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <path d="M8 12h8M12 8v8" />
            </svg>
            {{ __('Full Editor') }}
        </button>
    </div>

    <p class="medt-panel-tip">
        <span class="medt-tip-badge">INFO</span>&nbsp;
        {{ __('Click formula → auto-copy → Ctrl+V in editor. Format: \( formula \)') }}
    </p>
</div>
