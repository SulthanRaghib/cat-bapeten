{{-- ══════════════════════════════════════════════════════
     MODAL EDITOR LENGKAP
     ══════════════════════════════════════════════════════ --}}
<div x-show="showEditor" x-cloak class="medt-overlay" @click.self="closeEditor()" @keydown.escape.window="closeEditor()"
    role="dialog" aria-modal="true" aria-label="Editor Rumus Matematika">

    <div class="medt-dialog">

        {{-- ── Header dialog ── --}}
        <div class="medt-dialog-head">
            <div class="medt-dialog-title">
                <span class="medt-dialog-icon" aria-hidden="true">∑</span>
                {{ __('Math Formula Helper') }}
            </div>
            <button type="button" class="medt-close" @click.stop="closeEditor()" title="{{ __('Close (Esc)') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- ── Body dialog: 2 kolom ── --}}
        <div class="medt-dialog-body">

            {{-- ─────────────────────────────────────
                 KOLOM KIRI — pemilih simbol/template
                 ───────────────────────────────────── --}}
            <div class="medt-sym-panel">

                {{-- Tab kategori --}}
                <div class="medt-tabs" role="tablist">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button type="button" class="medt-tab" role="tab"
                            :class="{ 'is-active': activeTab === tab.id }" @click.prevent="activeTab = tab.id"
                            x-text="tab.label">
                        </button>
                    </template>
                </div>

                {{-- Keterangan tab aktif --}}
                <p class="medt-tab-note" x-text="activeTabNote"></p>

                {{-- Grid simbol / template --}}
                <div class="medt-gallery">
                    <template x-for="sym in currentSymbols" :key="sym.id">
                        <button type="button" class="medt-sym-btn" :class="{ 'is-tpl': sym.isTemplate }"
                            :title="sym.desc || sym.name" @click.prevent="insertSym(sym)">
                            <span class="medt-sym-math" x-html="kr(sym.display || sym.latex, !!sym.isTemplate)">
                            </span>
                            <span class="medt-sym-name" x-text="sym.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- ─────────────────────────────────────
                 KOLOM KANAN — input LaTeX + pratinjau
                 ───────────────────────────────────── --}}
            <div class="medt-compose">

                {{-- Input LaTeX --}}
                {{-- Toolbar: label + aksi --}}
                <div class="medt-compose-row">
                    <label class="medt-label" style="margin:0">✏️ Kode LaTeX <span class="medt-label-sub">ketik atau
                            klik simbol</span></label>
                    <div class="medt-toolbar-actions">
                        {{-- Undo --}}
                        <button type="button" class="medt-btn-icon" @click.prevent="undo()" :disabled="!canUndo"
                            title="Undo (Ctrl+Z)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7v6h6" />
                                <path d="M3 13A9 9 0 1 0 6 6.7" />
                            </svg>
                        </button>
                        {{-- Redo --}}
                        <button type="button" class="medt-btn-icon" @click.prevent="redo()" :disabled="!canRedo"
                            title="Redo (Ctrl+Y)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 7v6h-6" />
                                <path d="M21 13A9 9 0 1 1 18 6.7" />
                            </svg>
                        </button>
                        {{-- Hapus --}}
                        <button type="button" class="medt-btn-clear" @click.prevent="clearInput()"
                            title="{{ __('Clear all') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                            </svg>
                            {{ __('Clear') }}
                        </button>
                    </div>
                </div>

                <textarea x-ref="latexInput" x-model="latexInput" @input="onInput()" @keydown.tab.prevent="onTab()"
                    @keydown.ctrl.z.prevent="undo()" @keydown.ctrl.y.prevent="redo()" x-init="$nextTick(() => autoResize())" class="medt-textarea"
                    placeholder="Contoh: \frac{a}{b} atau klik simbol di kiri…" spellcheck="false" autocomplete="off">
                </textarea>
                <p class="medt-input-hint">Tab = indentasi &nbsp;·&nbsp; <kbd>Ctrl+Z</kbd> Undo &nbsp;·&nbsp;
                    <kbd>Ctrl+Y</kbd> Redo
                </p>

                {{-- Pratinjau visual (KaTeX render sesungguhnya) --}}
                <label class="medt-label" style="margin-top:14px">🔍 {{ __('Visual Preview') }}</label>

                <div class="medt-preview">
                    {{-- State kosong --}}
                    <div x-show="!latexInput.trim()" class="medt-preview-empty">
                        <span class="medt-preview-empty-icon">∑</span>
                        <span>{{ __('Formula preview appears here') }}</span>
                    </div>

                    {{-- Scroll wrapper: formula bisa di-scroll horizontal jika terlalu panjang --}}
                    <div x-show="latexInput.trim() && !previewError" class="medt-preview-scroll">
                        <div class="medt-preview-render" x-html="previewHtml"></div>
                    </div>

                    {{-- Pesan error sintaks --}}
                    <div x-show="previewError" class="medt-preview-error" x-text="previewError">
                    </div>
                </div>
                <p x-show="latexInput.trim() && !previewError" class="medt-preview-hint">
                    <span x-show="displayMode === 'inline'">Mode: <code>\( rumus \)</code> — sebaris</span>
                    <span x-show="displayMode === 'display'">Mode: <code>\[ rumus \]</code> — blok tengah</span>
                    &nbsp;·&nbsp; Geser ← → untuk lihat rumus panjang
                </p>

                {{-- Pilih mode tampil --}}
                <div class="medt-mode-row">
                    <span class="medt-label" style="margin:0;flex-shrink:0">Mode:</span>
                    <div class="medt-mode-toggle">
                        <button type="button" class="medt-mode-btn"
                            :class="{ 'is-active': displayMode === 'inline' }" @click.prevent="setMode('inline')"
                            title="{{ __('Inline') }}">
                            {{ __('Inline') }}&nbsp;<code>\(…\)</code>
                        </button>
                        <button type="button" class="medt-mode-btn"
                            :class="{ 'is-active': displayMode === 'display' }" @click.prevent="setMode('display')"
                            title="{{ __('Center Block') }}">
                            {{ __('Center Block') }}&nbsp;<code>\[…\]</code>
                        </button>
                    </div>
                </div>

                {{-- Tombol salin --}}
                <button type="button" class="medt-copy-btn" :disabled="!latexInput.trim()"
                    @click.prevent="copyFormula()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" />
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                    </svg>
                    <span x-text="copyLabel"></span>
                </button>

                {{-- Panduan compact --}}
                <div class="medt-steps">
                    <span class="medt-steps-title">📋 {{ __('How to use') }}</span>
                    <span
                        class="medt-steps-inline">{{ __('Select symbol → preview → Copy Formula → Ctrl+V in editor.') }}</span>
                </div>

            </div>{{-- /medt-compose --}}

        </div>{{-- /medt-dialog-body --}}
    </div>{{-- /medt-dialog --}}
</div>{{-- /medt-overlay --}}
