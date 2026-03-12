{{-- ============================================================
     Komponen Editor Rumus Matematika v2.0
     Teknologi : Alpine.js v3 + KaTeX (render LaTeX real-time)
     Penggunaan: View::make('filament.components.math-helper')
     ============================================================ --}}

@php
    $medtI18n = [
        'copyFormula' => __('Copy Formula'),
        'copied' => __('✅ Copied!'),
        'copyFailed' => __('❌ Failed to copy'),
        'fraction' => __('Fraction'),
        'power' => __('Power'),
        'root' => __('Root'),
        'operators' => __('Operators'),
        'structure' => __('Structure'),
        'greek' => __('Greek'),
        'functions' => __('Functions'),
        'nuclear' => __('Nuclear'),
        'atom' => __('Atom'),
        'molecule' => __('Molecule'),
        'noteOperators' => __('Click a symbol to insert at the cursor position.'),
        'noteStructure' => __('Ready-to-use templates — click to insert, then replace placeholder letters.'),
        'noteGreek' => __('Greek alphabet letters — click to insert.'),
        'noteFunctions' => __('Standard math functions — click to insert.'),
        'noteNuclear' => __('Physics and radiation safety formulas for BAPETEN context.'),
        'noteAtom' => __('Nuclide notation & element symbols — click to insert.'),
        'noteMolecule' => __('Molecular formulas & chemical compounds — click to insert.'),
    ];
@endphp

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.css" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.js" crossorigin="anonymous"></script>
    @vite(['resources/css/components/math-helper.css', 'resources/js/components/math-helper.js'])
@endonce

{{-- ── Root wrapper ── --}}
<div x-data="mathEditorV2()" x-init="init()" class="medt-wrapper" data-medt-i18n='@json($medtI18n)'>
    @include('filament.components.math-helper.panel')
    @include('filament.components.math-helper.modal')
</div>
