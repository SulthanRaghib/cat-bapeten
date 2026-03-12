(() => {
    const DEFAULT_I18N = {
        copyFormula: 'Copy Formula',
        copied: '✅ Copied!',
        copyFailed: '❌ Failed to copy',
        fraction: 'Fraction',
        power: 'Power',
        root: 'Root',
        operators: 'Operators',
        structure: 'Structure',
        greek: 'Greek',
        functions: 'Functions',
        nuclear: 'Nuclear',
        atom: 'Atom',
        molecule: 'Molecule',
        noteOperators: 'Click a symbol to insert at the cursor position.',
        noteStructure: 'Ready-to-use templates — click to insert, then replace placeholder letters.',
        noteGreek: 'Greek alphabet letters — click to insert.',
        noteFunctions: 'Standard math functions — click to insert.',
        noteNuclear: 'Physics and radiation safety formulas for BAPETEN context.',
        noteAtom: 'Nuclide notation & element symbols — click to insert.',
        noteMolecule: 'Molecular formulas & chemical compounds — click to insert.',
    };

    const buildQuickChips = (i18n) => [
        { id: 'frac', name: i18n.fraction, latex: '\\frac{a}{b}' },
        { id: 'pow', name: i18n.power, latex: 'x^{2}' },
        { id: 'sqrt', name: i18n.root, latex: '\\sqrt{x}' },
        { id: 'sum', name: 'Sigma', latex: '\\sum_{i=1}^{n} x_i' },
        { id: 'int', name: 'Integral', latex: '\\int_{a}^{b} f(x)\\,dx' },
        { id: 'pi', name: 'Pi', latex: '\\pi' },
        { id: 'alpha', name: 'Alpha', latex: '\\alpha' },
        { id: 'delta', name: 'Delta', latex: '\\Delta' },
    ];

    const buildTabs = (i18n) => [
        { id: 'operators', label: `± ${i18n.operators}`, note: i18n.noteOperators },
        { id: 'structure', label: `∫ ${i18n.structure}`, note: i18n.noteStructure },
        { id: 'greek', label: `α ${i18n.greek}`, note: i18n.noteGreek },
        { id: 'functions', label: `sin ${i18n.functions}`, note: i18n.noteFunctions },
        { id: 'nuclear', label: `⚛ ${i18n.nuclear}`, note: i18n.noteNuclear },
        { id: 'atom', label: `⚗ ${i18n.atom}`, note: i18n.noteAtom },
        { id: 'molecule', label: `🧪 ${i18n.molecule}`, note: i18n.noteMolecule },
    ];

    const SYMBOLS = {
        operators: [
            { id: 'pm', name: '±', latex: ' \\pm ', desc: 'Plus minus' },
            { id: 'times', name: '×', latex: ' \\times ', desc: 'Perkalian' },
            { id: 'div', name: '÷', latex: ' \\div ', desc: 'Pembagian' },
            { id: 'neq', name: '≠', latex: ' \\neq ', desc: 'Tidak sama' },
            { id: 'approx', name: '≈', latex: ' \\approx ', desc: 'Kira-kira' },
            { id: 'leq', name: '≤', latex: ' \\leq ', desc: 'Lebih kecil sama dengan' },
            { id: 'geq', name: '≥', latex: ' \\geq ', desc: 'Lebih besar sama dengan' },
            { id: 'infty', name: '∞', latex: ' \\infty ', desc: 'Tak hingga' },
            { id: 'deg', name: '°', latex: '^{\\circ}', desc: 'Derajat' },
            { id: 'propto', name: '∝', latex: ' \\propto ', desc: 'Berbanding lurus' },
            { id: 'sim', name: '~', latex: ' \\sim ', desc: 'Sebanding/mirip' },
            { id: 'equiv', name: '≡', latex: ' \\equiv ', desc: 'Kongruen / identik' },
            { id: 'in', name: '∈', latex: ' \\in ', desc: 'Elemen dari' },
            { id: 'notin', name: '∉', latex: ' \\notin ', desc: 'Bukan elemen dari' },
            { id: 'subset', name: '⊂', latex: ' \\subset ', desc: 'Himpunan bagian' },
            { id: 'perp', name: '⊥', latex: ' \\perp ', desc: 'Tegak lurus' },
            { id: 'parallel', name: '∥', latex: ' \\parallel ', desc: 'Sejajar' },
            { id: 'angle', name: '∠', latex: ' \\angle ', desc: 'Sudut' },
            { id: 'triangle', name: '△', latex: ' \\triangle ', desc: 'Segitiga' },
            { id: 'nabla', name: '∇', latex: ' \\nabla ', desc: 'Nabla/gradien' },
            { id: 'cdot', name: '·', latex: ' \\cdot ', desc: 'Titik (perkalian)' },
            { id: 'circ', name: '○', latex: ' \\circ ', desc: 'Komposisi fungsi' },
        ],

        structure: [
            { id: 'frac', name: 'Pecahan', isTemplate: true, latex: '\\frac{a}{b}', display: '\\frac{a}{b}' },
            { id: 'dfrac', name: 'Pecahan Besar', isTemplate: true, latex: '\\dfrac{a}{b}', display: '\\frac{a}{b}' },
            { id: 'frac2', name: 'Pecahan Bertingkat', isTemplate: true, latex: '\\frac{\\frac{a}{b}}{c}', display: '\\frac{a/b}{c}' },
            { id: 'sqrt', name: 'Akar √', isTemplate: true, latex: '\\sqrt{x}', display: '\\sqrt{x}' },
            { id: 'sqrtn', name: 'Akar ke-n', isTemplate: true, latex: '\\sqrt[n]{x}', display: '\\sqrt[n]{x}' },
            { id: 'pow', name: 'Pangkat', isTemplate: true, latex: 'x^{n}', display: 'x^{n}' },
            { id: 'sub', name: 'Subscript', isTemplate: true, latex: 'x_{i}', display: 'x_{i}' },
            { id: 'subpow', name: 'Sub + Pangkat', isTemplate: true, latex: 'x_{i}^{n}', display: 'x_{i}^{n}' },
            { id: 'int_def', name: 'Integral Tentu', isTemplate: true, latex: '\\int_{a}^{b} f(x)\\,dx', display: '\\int_{a}^{b}' },
            { id: 'int_indef', name: 'Integral Tak Tentu', isTemplate: true, latex: '\\int f(x)\\,dx', display: '\\int f\\,dx' },
            { id: 'sum', name: 'Sigma Σ', isTemplate: true, latex: '\\sum_{i=1}^{n} x_i', display: '\\sum_{i}^{n}' },
            { id: 'prod', name: 'Produk Π', isTemplate: true, latex: '\\prod_{i=1}^{n} x_i', display: '\\prod_{i}^{n}' },
            { id: 'lim', name: 'Limit', isTemplate: true, latex: '\\lim_{x \\to 0} f(x)', display: '\\lim_{x\\to 0}' },
            { id: 'deriv', name: 'Turunan', isTemplate: true, latex: '\\frac{d}{dx} f(x)', display: '\\frac{d}{dx}' },
            { id: 'partial', name: 'Turunan Parsial', isTemplate: true, latex: '\\frac{\\partial f}{\\partial x}', display: '\\frac{\\partial}{\\partial x}' },
            { id: 'mat2', name: 'Matriks 2×2', isTemplate: true, latex: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}', display: '\\begin{pmatrix}a&b\\\\c&d\\end{pmatrix}' },
            { id: 'abs', name: 'Nilai Mutlak |x|', isTemplate: true, latex: '\\left| x \\right|', display: '|x|' },
            { id: 'vec', name: 'Vektor', isTemplate: true, latex: '\\vec{v}', display: '\\vec{v}' },
            { id: 'overline', name: 'Garis Atas', isTemplate: true, latex: '\\overline{AB}', display: '\\overline{AB}' },
            { id: 'hat', name: 'Topi', isTemplate: true, latex: '\\hat{x}', display: '\\hat{x}' },
        ],

        greek: [
            { id: 'alpha', name: 'alpha', latex: '\\alpha ' },
            { id: 'beta', name: 'beta', latex: '\\beta ' },
            { id: 'gamma', name: 'gamma', latex: '\\gamma ' },
            { id: 'delta_l', name: 'delta', latex: '\\delta ' },
            { id: 'epsilon', name: 'epsilon', latex: '\\epsilon ' },
            { id: 'zeta', name: 'zeta', latex: '\\zeta ' },
            { id: 'eta', name: 'eta', latex: '\\eta ' },
            { id: 'theta', name: 'theta', latex: '\\theta ' },
            { id: 'kappa', name: 'kappa', latex: '\\kappa ' },
            { id: 'lambda', name: 'lambda', latex: '\\lambda ' },
            { id: 'mu', name: 'mu', latex: '\\mu ' },
            { id: 'nu', name: 'nu', latex: '\\nu ' },
            { id: 'xi', name: 'xi', latex: '\\xi ' },
            { id: 'pi_l', name: 'pi', latex: '\\pi ' },
            { id: 'rho', name: 'rho', latex: '\\rho ' },
            { id: 'sigma_l', name: 'sigma', latex: '\\sigma ' },
            { id: 'tau', name: 'tau', latex: '\\tau ' },
            { id: 'phi', name: 'phi', latex: '\\phi ' },
            { id: 'chi', name: 'chi', latex: '\\chi ' },
            { id: 'psi', name: 'psi', latex: '\\psi ' },
            { id: 'omega', name: 'omega', latex: '\\omega ' },
            { id: 'Gamma', name: 'Gamma', latex: '\\Gamma ' },
            { id: 'Delta', name: 'Delta', latex: '\\Delta ' },
            { id: 'Theta', name: 'Theta', latex: '\\Theta ' },
            { id: 'Lambda', name: 'Lambda', latex: '\\Lambda ' },
            { id: 'Xi', name: 'Xi', latex: '\\Xi ' },
            { id: 'Pi', name: 'Pi', latex: '\\Pi ' },
            { id: 'Sigma', name: 'Sigma', latex: '\\Sigma ' },
            { id: 'Phi', name: 'Phi', latex: '\\Phi ' },
            { id: 'Psi', name: 'Psi', latex: '\\Psi ' },
            { id: 'Omega', name: 'Omega', latex: '\\Omega ' },
        ],

        functions: [
            { id: 'sin', name: 'sin', latex: '\\sin ' },
            { id: 'cos', name: 'cos', latex: '\\cos ' },
            { id: 'tan', name: 'tan', latex: '\\tan ' },
            { id: 'cot', name: 'cot', latex: '\\cot ' },
            { id: 'sec', name: 'sec', latex: '\\sec ' },
            { id: 'csc', name: 'csc', latex: '\\csc ' },
            { id: 'arcsin', name: 'arcsin', latex: '\\arcsin ' },
            { id: 'arccos', name: 'arccos', latex: '\\arccos ' },
            { id: 'arctan', name: 'arctan', latex: '\\arctan ' },
            { id: 'log', name: 'log', latex: '\\log ' },
            { id: 'log10', name: 'log₁₀', latex: '\\log_{10} ' },
            { id: 'ln', name: 'ln', latex: '\\ln ' },
            { id: 'exp', name: 'exp', latex: '\\exp ' },
            { id: 'abs_fn', name: '|x|', isTemplate: true, latex: '\\left| x \\right|', display: '|x|' },
            { id: 'norm_fn', name: '‖x‖', isTemplate: true, latex: '\\left\\| x \\right\\|', display: '\\|x\\|' },
            { id: 'floor', name: '⌊x⌋', isTemplate: true, latex: '\\lfloor x \\rfloor', display: '\\lfloor x\\rfloor' },
            { id: 'ceil', name: '⌈x⌉', isTemplate: true, latex: '\\lceil x \\rceil', display: '\\lceil x\\rceil' },
            { id: 'paren', name: '(x)', isTemplate: true, latex: '\\left( x \\right)', display: '(x)' },
            { id: 'brack', name: '[x]', isTemplate: true, latex: '\\left[ x \\right]', display: '[x]' },
        ],

        nuclear: [
            { id: 'n_dose', name: 'Dosis D', isTemplate: true, latex: 'D = \\frac{E}{m}', desc: 'Dosis serap (Gy)' },
            { id: 'n_act', name: 'Aktivitas A', isTemplate: true, latex: 'A = \\lambda N', desc: 'Aktivitas radionuklida (Bq)' },
            { id: 'n_decay', name: 'Peluruhan', isTemplate: true, latex: 'N(t) = N_0 e^{-\\lambda t}', desc: 'Hukum peluruhan radioaktif' },
            { id: 'n_half', name: 'Waktu Paro', isTemplate: true, latex: 't_{1/2} = \\frac{\\ln 2}{\\lambda}', desc: 'Waktu paruh (T½)' },
            { id: 'n_emc2', name: 'E = mc²', isTemplate: true, latex: 'E = mc^{2}', desc: 'Kesetaraan massa-energi' },
            { id: 'n_inv', name: 'Hk. Invers Kuadrat', isTemplate: true, latex: 'I = \\frac{I_0}{r^2}', desc: 'Hukum invers kuadrat jarak' },
            { id: 'n_flux', name: 'Fluks Neutron', isTemplate: true, latex: '\\Phi = \\frac{N}{A \\cdot t}', desc: 'Fluks neutron (n/cm²·s)' },
            { id: 'n_equiv', name: 'Dosis Ekivalen H', isTemplate: true, latex: 'H = D \\cdot w_R', desc: 'Dosis ekivalen (Sv)' },
            { id: 'n_drate', name: 'Laju Dosis', isTemplate: true, latex: '\\dot{D} = \\frac{dD}{dt}', desc: 'Laju dosis serap (Gy/s)' },
            { id: 'n_atten', name: 'Atenuasi', isTemplate: true, latex: 'I = I_0 \\, e^{-\\mu x}', desc: 'Atenuasi radiasi foton' },
            { id: 'n_hvl', name: 'HVL (Paro Hamparan)', isTemplate: true, latex: 'HVL = \\frac{\\ln 2}{\\mu}', desc: 'Half Value Layer' },
            { id: 'n_buildup', name: 'Faktor Build-up', isTemplate: true, latex: 'D = B \\cdot D_0 \\cdot e^{-\\mu x}', desc: 'Faktor build-up radiasi' },
            { id: 'n_teff', name: 'T Efektif', isTemplate: true, latex: '\\frac{1}{T_{eff}} = \\frac{1}{T_{bio}} + \\frac{1}{T_{1/2}}', desc: 'Waktu paro efektif' },
            { id: 'n_conc', name: 'Konsentrasi', isTemplate: true, latex: 'C = \\frac{A}{V}', desc: 'Konsentrasi aktivitas (Bq/m³)' },
            { id: 'n_compton', name: 'Hamburan Compton', isTemplate: true, latex: "E' = \\frac{E_0}{1 + \\frac{E_0}{m_e c^2}(1-\\cos\\theta)}", desc: 'Energi foton setelah hamburan Compton' },
        ],

        atom: [
            { id: 'nuclide_tpl', name: 'Nuklida Umum', isTemplate: true, latex: '{}_{Z}^{A}\\text{El}', display: '{}_{Z}^{A}\\text{El}', desc: 'Template notasi nuklida umum' },
            { id: 'proton', name: 'Proton p', isTemplate: true, latex: '{}_{1}^{1}\\text{p}', display: '{}_{1}^{1}\\text{p}', desc: 'Proton' },
            { id: 'neutron', name: 'Neutron n', isTemplate: true, latex: '{}_{0}^{1}\\text{n}', display: '{}_{0}^{1}\\text{n}', desc: 'Neutron' },
            { id: 'electron', name: 'Elektron e⁻', isTemplate: true, latex: '{}_{-1}^{\\;0}\\text{e}', display: '{}_{-1}^{\\;0}\\text{e}', desc: 'Elektron' },
            { id: 'positron', name: 'Positron e⁺', isTemplate: true, latex: '{}_{+1}^{\\;0}\\text{e}', display: '{}_{+1}^{\\;0}\\text{e}', desc: 'Positron' },
            { id: 'alpha_p', name: 'α Partikel', isTemplate: true, latex: '{}_{2}^{4}\\text{He}', display: '{}_{2}^{4}\\text{He}', desc: 'Partikel alfa' },
            { id: 'beta_m', name: 'β⁻ Partikel', isTemplate: true, latex: '{}_{-1}^{\\;0}\\beta^{-}', display: '\\beta^{-}', desc: 'Beta minus' },
            { id: 'beta_p', name: 'β⁺ Positron', isTemplate: true, latex: '{}_{+1}^{\\;0}\\beta^{+}', display: '\\beta^{+}', desc: 'Beta plus' },
            { id: 'gamma_q', name: 'γ Foton', isTemplate: true, latex: '{}_{0}^{0}\\gamma', display: '\\gamma', desc: 'Foton gamma' },
            { id: 'neutrino', name: 'Anti-ν Neutrino', isTemplate: true, latex: '\\bar{\\nu}_{e}', display: '\\bar{\\nu}_{e}', desc: 'Anti-neutrino elektron' },
            { id: 'H1', name: '¹H Protium', isTemplate: true, latex: '{}_{1}^{1}\\text{H}', display: '{}_{1}^{1}\\text{H}', desc: 'Protium' },
            { id: 'H2', name: '²H Deuterium', isTemplate: true, latex: '{}_{1}^{2}\\text{H}', display: '{}_{1}^{2}\\text{H}', desc: 'Deuterium (D)' },
            { id: 'H3', name: '³H Tritium', isTemplate: true, latex: '{}_{1}^{3}\\text{H}', display: '{}_{1}^{3}\\text{H}', desc: 'Tritium (T)' },
            { id: 'He3', name: '³He', isTemplate: true, latex: '{}_{2}^{3}\\text{He}', display: '{}_{2}^{3}\\text{He}', desc: 'Helium-3' },
            { id: 'He4', name: '⁴He', isTemplate: true, latex: '{}_{2}^{4}\\text{He}', display: '{}_{2}^{4}\\text{He}', desc: 'Helium-4' },
            { id: 'C12', name: '¹²C', isTemplate: true, latex: '{}_{6}^{12}\\text{C}', display: '{}_{6}^{12}\\text{C}', desc: 'Karbon-12' },
            { id: 'C13', name: '¹³C', isTemplate: true, latex: '{}_{6}^{13}\\text{C}', display: '{}_{6}^{13}\\text{C}', desc: 'Karbon-13' },
            { id: 'C14', name: '¹⁴C', isTemplate: true, latex: '{}_{6}^{14}\\text{C}', display: '{}_{6}^{14}\\text{C}', desc: 'Karbon-14 (radioaktif)' },
            { id: 'N14', name: '¹⁴N', isTemplate: true, latex: '{}_{7}^{14}\\text{N}', display: '{}_{7}^{14}\\text{N}', desc: 'Nitrogen-14' },
            { id: 'O16', name: '¹⁶O', isTemplate: true, latex: '{}_{8}^{16}\\text{O}', display: '{}_{8}^{16}\\text{O}', desc: 'Oksigen-16' },
            { id: 'O18', name: '¹⁸O', isTemplate: true, latex: '{}_{8}^{18}\\text{O}', display: '{}_{8}^{18}\\text{O}', desc: 'Oksigen-18' },
            { id: 'Na23', name: '²³Na', isTemplate: true, latex: '{}_{11}^{23}\\text{Na}', display: '{}_{11}^{23}\\text{Na}', desc: 'Natrium-23' },
            { id: 'Co60', name: '⁶⁰Co', isTemplate: true, latex: '{}_{27}^{60}\\text{Co}', display: '{}_{27}^{60}\\text{Co}', desc: 'Kobalt-60' },
            { id: 'Sr90', name: '⁹⁰Sr', isTemplate: true, latex: '{}_{38}^{90}\\text{Sr}', display: '{}_{38}^{90}\\text{Sr}', desc: 'Strontium-90' },
            { id: 'Y90', name: '⁹⁰Y', isTemplate: true, latex: '{}_{39}^{90}\\text{Y}', display: '{}_{39}^{90}\\text{Y}', desc: 'Yttrium-90' },
            { id: 'Tc99m', name: '⁹⁹ᵐTc', isTemplate: true, latex: '{}_{43}^{99m}\\text{Tc}', display: '{}_{43}^{99m}\\text{Tc}', desc: 'Teknesium-99m' },
            { id: 'I131', name: '¹³¹I', isTemplate: true, latex: '{}_{53}^{131}\\text{I}', display: '{}_{53}^{131}\\text{I}', desc: 'Iodium-131' },
            { id: 'I125', name: '¹²⁵I', isTemplate: true, latex: '{}_{53}^{125}\\text{I}', display: '{}_{53}^{125}\\text{I}', desc: 'Iodium-125' },
            { id: 'Cs137', name: '¹³⁷Cs', isTemplate: true, latex: '{}_{55}^{137}\\text{Cs}', display: '{}_{55}^{137}\\text{Cs}', desc: 'Sesium-137' },
            { id: 'Ba137', name: '¹³⁷Ba', isTemplate: true, latex: '{}_{56}^{137}\\text{Ba}', display: '{}_{56}^{137}\\text{Ba}', desc: 'Barium-137' },
            { id: 'Au198', name: '¹⁹⁸Au', isTemplate: true, latex: '{}_{79}^{198}\\text{Au}', display: '{}_{79}^{198}\\text{Au}', desc: 'Emas-198' },
            { id: 'Pb208', name: '²⁰⁸Pb', isTemplate: true, latex: '{}_{82}^{208}\\text{Pb}', display: '{}_{82}^{208}\\text{Pb}', desc: 'Timbal-208' },
            { id: 'Po210', name: '²¹⁰Po', isTemplate: true, latex: '{}_{84}^{210}\\text{Po}', display: '{}_{84}^{210}\\text{Po}', desc: 'Polonium-210' },
            { id: 'Rn222', name: '²²²Rn', isTemplate: true, latex: '{}_{86}^{222}\\text{Rn}', display: '{}_{86}^{222}\\text{Rn}', desc: 'Radon-222' },
            { id: 'Ra226', name: '²²⁶Ra', isTemplate: true, latex: '{}_{88}^{226}\\text{Ra}', display: '{}_{88}^{226}\\text{Ra}', desc: 'Radium-226' },
            { id: 'Th232', name: '²³²Th', isTemplate: true, latex: '{}_{90}^{232}\\text{Th}', display: '{}_{90}^{232}\\text{Th}', desc: 'Torium-232' },
            { id: 'U235', name: '²³⁵U', isTemplate: true, latex: '{}_{92}^{235}\\text{U}', display: '{}_{92}^{235}\\text{U}', desc: 'Uranium-235' },
            { id: 'U238', name: '²³⁸U', isTemplate: true, latex: '{}_{92}^{238}\\text{U}', display: '{}_{92}^{238}\\text{U}', desc: 'Uranium-238' },
            { id: 'Pu239', name: '²³⁹Pu', isTemplate: true, latex: '{}_{94}^{239}\\text{Pu}', display: '{}_{94}^{239}\\text{Pu}', desc: 'Plutonium-239' },
            { id: 'Am241', name: '²⁴¹Am', isTemplate: true, latex: '{}_{95}^{241}\\text{Am}', display: '{}_{95}^{241}\\text{Am}', desc: 'Amerisium-241' },
            { id: 'decay_alpha', name: 'Peluruhan α', isTemplate: true, latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z-2}^{A-4}\\text{Y} + {}_{2}^{4}\\text{He}', display: '\\text{X}\\rightarrow\\text{Y}+\\alpha', desc: 'Reaksi peluruhan alfa' },
            { id: 'decay_beta', name: 'Peluruhan β⁻', isTemplate: true, latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z+1}^{A}\\text{Y} + {}_{-1}^{\\;0}\\beta^{-} + \\bar{\\nu}_{e}', display: '\\text{X}\\rightarrow\\text{Y}+\\beta^{-}', desc: 'Reaksi peluruhan beta minus' },
            { id: 'decay_betap', name: 'Peluruhan β⁺', isTemplate: true, latex: '{}_{Z}^{A}\\text{X} \\rightarrow {}_{Z-1}^{A}\\text{Y} + {}_{+1}^{\\;0}\\beta^{+} + \\nu_{e}', display: '\\text{X}\\rightarrow\\text{Y}+\\beta^{+}', desc: 'Reaksi peluruhan beta plus' },
            { id: 'fission', name: 'Fisi U-235', isTemplate: true, latex: '{}_{92}^{235}\\text{U} + {}_{0}^{1}\\text{n} \\rightarrow {}_{56}^{141}\\text{Ba} + {}_{36}^{92}\\text{Kr} + 3\\,{}_{0}^{1}\\text{n}', display: '\\text{U}+\\text{n}\\rightarrow\\text{fisi}', desc: 'Contoh reaksi fisi U-235' },
            { id: 'fusion', name: 'Fusi D-T', isTemplate: true, latex: '{}_{1}^{2}\\text{H} + {}_{1}^{3}\\text{H} \\rightarrow {}_{2}^{4}\\text{He} + {}_{0}^{1}\\text{n}', display: '\\text{D}+\\text{T}\\rightarrow\\text{He}+\\text{n}', desc: 'Reaksi fusi deuterium-tritium' },
        ],

        molecule: [
            { id: 'm_h2', name: 'H₂', isTemplate: true, latex: '\\text{H}_2', display: '\\text{H}_2', desc: 'Gas hidrogen' },
            { id: 'm_o2', name: 'O₂', isTemplate: true, latex: '\\text{O}_2', display: '\\text{O}_2', desc: 'Gas oksigen' },
            { id: 'm_n2', name: 'N₂', isTemplate: true, latex: '\\text{N}_2', display: '\\text{N}_2', desc: 'Gas nitrogen' },
            { id: 'm_o3', name: 'O₃ Ozon', isTemplate: true, latex: '\\text{O}_3', display: '\\text{O}_3', desc: 'Ozon' },
            { id: 'm_co2', name: 'CO₂', isTemplate: true, latex: '\\text{CO}_2', display: '\\text{CO}_2', desc: 'Karbon dioksida' },
            { id: 'm_co', name: 'CO', isTemplate: true, latex: '\\text{CO}', display: '\\text{CO}', desc: 'Karbon monoksida' },
            { id: 'm_h2o', name: 'H₂O Air', isTemplate: true, latex: '\\text{H}_2\\text{O}', display: '\\text{H}_2\\text{O}', desc: 'Air' },
            { id: 'm_h2o2', name: 'H₂O₂', isTemplate: true, latex: '\\text{H}_2\\text{O}_2', display: '\\text{H}_2\\text{O}_2', desc: 'Hidrogen peroksida' },
            { id: 'm_nh3', name: 'NH₃ Amonia', isTemplate: true, latex: '\\text{NH}_3', display: '\\text{NH}_3', desc: 'Amonia' },
            { id: 'm_ch4', name: 'CH₄ Metana', isTemplate: true, latex: '\\text{CH}_4', display: '\\text{CH}_4', desc: 'Gas metana' },
            { id: 'm_no2', name: 'NO₂', isTemplate: true, latex: '\\text{NO}_2', display: '\\text{NO}_2', desc: 'Nitrogen dioksida' },
            { id: 'm_so2', name: 'SO₂', isTemplate: true, latex: '\\text{SO}_2', display: '\\text{SO}_2', desc: 'Sulfur dioksida' },
            { id: 'm_so3', name: 'SO₃', isTemplate: true, latex: '\\text{SO}_3', display: '\\text{SO}_3', desc: 'Sulfur trioksida' },
            { id: 'm_h2s', name: 'H₂S', isTemplate: true, latex: '\\text{H}_2\\text{S}', display: '\\text{H}_2\\text{S}', desc: 'Hidrogen sulfida' },
            { id: 'm_cl2', name: 'Cl₂', isTemplate: true, latex: '\\text{Cl}_2', display: '\\text{Cl}_2', desc: 'Gas klorin' },
            { id: 'm_f2', name: 'F₂', isTemplate: true, latex: '\\text{F}_2', display: '\\text{F}_2', desc: 'Gas fluor' },
            { id: 'm_hcl', name: 'HCl', isTemplate: true, latex: '\\text{HCl}', display: '\\text{HCl}', desc: 'Asam klorida' },
            { id: 'm_hf', name: 'HF', isTemplate: true, latex: '\\text{HF}', display: '\\text{HF}', desc: 'Asam fluorida' },
            { id: 'm_hbr', name: 'HBr', isTemplate: true, latex: '\\text{HBr}', display: '\\text{HBr}', desc: 'Asam bromida' },
            { id: 'm_hi', name: 'HI', isTemplate: true, latex: '\\text{HI}', display: '\\text{HI}', desc: 'Asam iodida' },
            { id: 'm_hno3', name: 'HNO₃', isTemplate: true, latex: '\\text{HNO}_3', display: '\\text{HNO}_3', desc: 'Asam nitrat' },
            { id: 'm_h2so4', name: 'H₂SO₄', isTemplate: true, latex: '\\text{H}_2\\text{SO}_4', display: '\\text{H}_2\\text{SO}_4', desc: 'Asam sulfat' },
            { id: 'm_h3po4', name: 'H₃PO₄', isTemplate: true, latex: '\\text{H}_3\\text{PO}_4', display: '\\text{H}_3\\text{PO}_4', desc: 'Asam fosfat' },
            { id: 'm_hcooh', name: 'HCOOH', isTemplate: true, latex: '\\text{HCOOH}', display: '\\text{HCOOH}', desc: 'Asam formiat' },
            { id: 'm_acetic', name: 'CH₃COOH', isTemplate: true, latex: '\\text{CH}_3\\text{COOH}', display: '\\text{CH}_3\\text{COOH}', desc: 'Asam asetat' },
            { id: 'm_naoh', name: 'NaOH', isTemplate: true, latex: '\\text{NaOH}', display: '\\text{NaOH}', desc: 'Natrium hidroksida' },
            { id: 'm_koh', name: 'KOH', isTemplate: true, latex: '\\text{KOH}', display: '\\text{KOH}', desc: 'Kalium hidroksida' },
            { id: 'm_caoh2', name: 'Ca(OH)₂', isTemplate: true, latex: '\\text{Ca(OH)}_2', display: '\\text{Ca(OH)}_2', desc: 'Kalsium hidroksida' },
            { id: 'm_nh4oh', name: 'NH₄OH', isTemplate: true, latex: '\\text{NH}_4\\text{OH}', display: '\\text{NH}_4\\text{OH}', desc: 'Amonium hidroksida' },
            { id: 'm_nacl', name: 'NaCl Garam', isTemplate: true, latex: '\\text{NaCl}', display: '\\text{NaCl}', desc: 'Natrium klorida' },
            { id: 'm_kcl', name: 'KCl', isTemplate: true, latex: '\\text{KCl}', display: '\\text{KCl}', desc: 'Kalium klorida' },
            { id: 'm_caco3', name: 'CaCO₃', isTemplate: true, latex: '\\text{CaCO}_3', display: '\\text{CaCO}_3', desc: 'Kalsium karbonat' },
            { id: 'm_caso4', name: 'CaSO₄', isTemplate: true, latex: '\\text{CaSO}_4', display: '\\text{CaSO}_4', desc: 'Kalsium sulfat' },
            { id: 'm_na2co3', name: 'Na₂CO₃', isTemplate: true, latex: '\\text{Na}_2\\text{CO}_3', display: '\\text{Na}_2\\text{CO}_3', desc: 'Natrium karbonat / soda abu' },
            { id: 'm_nahco3', name: 'NaHCO₃', isTemplate: true, latex: '\\text{NaHCO}_3', display: '\\text{NaHCO}_3', desc: 'Natrium bikarbonat / soda kue' },
            { id: 'm_fecl3', name: 'FeCl₃', isTemplate: true, latex: '\\text{FeCl}_3', display: '\\text{FeCl}_3', desc: 'Besi(III) klorida' },
            { id: 'm_al2o3', name: 'Al₂O₃', isTemplate: true, latex: '\\text{Al}_2\\text{O}_3', display: '\\text{Al}_2\\text{O}_3', desc: 'Aluminium oksida' },
            { id: 'm_fe2o3', name: 'Fe₂O₃ Karat', isTemplate: true, latex: '\\text{Fe}_2\\text{O}_3', display: '\\text{Fe}_2\\text{O}_3', desc: 'Besi(III) oksida' },
            { id: 'm_mno2', name: 'MnO₂', isTemplate: true, latex: '\\text{MnO}_2', display: '\\text{MnO}_2', desc: 'Mangan(IV) oksida' },
            { id: 'm_sio2', name: 'SiO₂ Silika', isTemplate: true, latex: '\\text{SiO}_2', display: '\\text{SiO}_2', desc: 'Silikon dioksida' },
            { id: 'm_tio2', name: 'TiO₂', isTemplate: true, latex: '\\text{TiO}_2', display: '\\text{TiO}_2', desc: 'Titanium dioksida' },
            { id: 'm_uo2', name: 'UO₂', isTemplate: true, latex: '\\text{UO}_2', display: '\\text{UO}_2', desc: 'Uranium dioksida (bahan bakar nuklir)' },
            { id: 'm_u3o8', name: 'U₃O₈ Yellowcake', isTemplate: true, latex: '\\text{U}_3\\text{O}_8', display: '\\text{U}_3\\text{O}_8', desc: 'Triuranium oktoksida / yellowcake' },
            { id: 'm_uf6', name: 'UF₆ Pengayaan', isTemplate: true, latex: '\\text{UF}_6', display: '\\text{UF}_6', desc: 'Uranium heksafluorida (pengayaan)' },
            { id: 'm_puo2', name: 'PuO₂', isTemplate: true, latex: '\\text{PuO}_2', display: '\\text{PuO}_2', desc: 'Plutonium dioksida' },
            { id: 'm_tho2', name: 'ThO₂', isTemplate: true, latex: '\\text{ThO}_2', display: '\\text{ThO}_2', desc: 'Torium dioksida' },
            { id: 'm_ki', name: 'KI Tiroid', isTemplate: true, latex: '\\text{KI}', display: '\\text{KI}', desc: 'Kalium iodida (proteksi tiroid)' },
            { id: 'm_kio3', name: 'KIO₃', isTemplate: true, latex: '\\text{KIO}_3', display: '\\text{KIO}_3', desc: 'Kalium iodat (proteksi tiroid)' },
            { id: 'm_dtpa', name: 'Na-DTPA', isTemplate: true, latex: '\\text{Na}_5[\\text{DTPA}]', display: '\\text{Na}_5[\\text{DTPA}]', desc: 'Natrium DTPA (dekontaminasi internal)' },
            { id: 'm_edta', name: 'Na₂-EDTA', isTemplate: true, latex: '\\text{Na}_2[\\text{EDTA}]', display: '\\text{Na}_2[\\text{EDTA}]', desc: 'Dinatrium EDTA (chelating agent)' },
            { id: 'm_fdg', name: '¹⁸F-FDG', isTemplate: true, latex: '[^{18}\\text{F}]\\text{FDG}', display: '[{}^{18}\\text{F}]\\text{FDG}', desc: 'Fluorodeoksiglukosa (PET scan)' },
            { id: 'm_glucose', name: 'C₆H₁₂O₆', isTemplate: true, latex: '\\text{C}_6\\text{H}_{12}\\text{O}_6', display: '\\text{C}_6\\text{H}_{12}\\text{O}_6', desc: 'Glukosa' },
            { id: 'm_hplus', name: 'H⁺ Ion', isTemplate: true, latex: '\\text{H}^{+}', display: '\\text{H}^{+}', desc: 'Ion hidrogen' },
            { id: 'm_ohminus', name: 'OH⁻ Ion', isTemplate: true, latex: '\\text{OH}^{-}', display: '\\text{OH}^{-}', desc: 'Ion hidroksida' },
            { id: 'm_naplus', name: 'Na⁺', isTemplate: true, latex: '\\text{Na}^{+}', display: '\\text{Na}^{+}', desc: 'Ion natrium' },
            { id: 'm_clminus', name: 'Cl⁻', isTemplate: true, latex: '\\text{Cl}^{-}', display: '\\text{Cl}^{-}', desc: 'Ion klorida' },
            { id: 'm_ca2p', name: 'Ca²⁺', isTemplate: true, latex: '\\text{Ca}^{2+}', display: '\\text{Ca}^{2+}', desc: 'Ion kalsium' },
            { id: 'm_fe3p', name: 'Fe³⁺', isTemplate: true, latex: '\\text{Fe}^{3+}', display: '\\text{Fe}^{3+}', desc: 'Ion besi(III)' },
            { id: 'm_uo2ion', name: 'UO₂²⁺ Uranil', isTemplate: true, latex: '\\text{UO}_2^{2+}', display: '\\text{UO}_2^{2+}', desc: 'Ion uranil' },
            { id: 'm_ion_tpl', name: 'Xⁿ± Ion Umum', isTemplate: true, latex: '\\text{X}^{n\\pm}', display: '\\text{X}^{n\\pm}', desc: 'Template ion umum' },
            { id: 'm_rxn', name: 'Reaksi Umum', isTemplate: true, latex: '\\text{A} + \\text{B} \\rightarrow \\text{C} + \\text{D}', display: '\\text{A}+\\text{B}\\rightarrow\\text{C}', desc: 'Template reaksi kimia umum' },
            { id: 'm_equil', name: 'Kesetimbangan', isTemplate: true, latex: '\\text{A} + \\text{B} \\rightleftharpoons \\text{C} + \\text{D}', display: '\\text{A}\\rightleftharpoons\\text{C}', desc: 'Reaksi reversibel / kesetimbangan' },
            { id: 'm_neutr', name: 'Netralisasi', isTemplate: true, latex: '\\text{HCl} + \\text{NaOH} \\rightarrow \\text{NaCl} + \\text{H}_2\\text{O}', display: '\\text{HCl}+\\text{NaOH}\\rightarrow\\text{NaCl}', desc: 'Reaksi netralisasi asam-basa' },
            { id: 'm_elec', name: 'Elektrolisis Air', isTemplate: true, latex: '2\\,\\text{H}_2\\text{O} \\xrightarrow{\\text{elektrolisis}} 2\\,\\text{H}_2 + \\text{O}_2', display: '2\\text{H}_2\\text{O}\\rightarrow2\\text{H}_2+\\text{O}_2', desc: 'Elektrolisis air' },
            { id: 'm_combust', name: 'Pembakaran C', isTemplate: true, latex: '\\text{C} + \\text{O}_2 \\rightarrow \\text{CO}_2', display: '\\text{C}+\\text{O}_2\\rightarrow\\text{CO}_2', desc: 'Pembakaran karbon sempurna' },
        ],
    };

    function parseI18nOverrides(el) {
        if (!el || !el.dataset || !el.dataset.medtI18n) return {};
        try {
            return JSON.parse(el.dataset.medtI18n);
        } catch (_) {
            return {};
        }
    }

    function mathEditorV2Factory() {
        return {
            showEditor: false,
            latexInput: '',
            previewHtml: '',
            previewError: '',
            displayMode: 'inline',
            activeTab: 'operators',
            copyLabel: '',
            i18n: { ...DEFAULT_I18N },
            quickChips: [],
            tabs: [],
            symbols: SYMBOLS,
            _history: [''],
            _historyIndex: 0,
            _historyTimer: null,

            get currentSymbols() {
                return this.symbols[this.activeTab] || [];
            },

            get activeTabNote() {
                const t = this.tabs.find((tab) => tab.id === this.activeTab);
                return t ? t.note : '';
            },

            init() {
                this.applyI18n();

                const resetModal = () => {
                    this.showEditor = false;
                };

                if (typeof Livewire !== 'undefined') {
                    Livewire.hook('morph.updated', resetModal);
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            if (this.$el && !document.contains(this.$el)) return;
                        });
                    });
                }

                this.$el.addEventListener('livewire:update', resetModal);
                document.addEventListener('livewire:navigated', resetModal);
            },

            applyI18n() {
                const overrides = parseI18nOverrides(this.$el);
                this.i18n = { ...DEFAULT_I18N, ...overrides };
                this.quickChips = buildQuickChips(this.i18n);
                this.tabs = buildTabs(this.i18n);
                this.copyLabel = `📋 ${this.i18n.copyFormula}`;
            },

            kr(latex, block = false) {
                if (typeof katex === 'undefined') {
                    return `<code style="font-size:11px">${latex}</code>`;
                }

                const inner = latex
                    .trim()
                    .replace(/^\\\(/, '')
                    .replace(/\\\)$/, '')
                    .replace(/^\\\[/, '')
                    .replace(/\\\]$/, '')
                    .trim();

                try {
                    return katex.renderToString(inner, {
                        throwOnError: false,
                        displayMode: block,
                        output: 'html',
                    });
                } catch (e) {
                    return `<code style="font-size:10px">${inner}</code>`;
                }
            },

            updatePreview() {
                if (!this.latexInput.trim()) {
                    this.previewHtml = '';
                    this.previewError = '';
                    return;
                }

                if (typeof katex !== 'undefined') {
                    try {
                        this.previewHtml = katex.renderToString(this.latexInput.trim(), {
                            throwOnError: true,
                            displayMode: this.displayMode === 'display',
                            output: 'html',
                        });
                        this.previewError = '';
                    } catch (e) {
                        this.previewError = `⚠️ Sintaks LaTeX tidak valid: ${e.message.split('\n')[0]}`;
                        this.previewHtml = katex.renderToString(this.latexInput.trim(), {
                            throwOnError: false,
                            displayMode: this.displayMode === 'display',
                            output: 'html',
                        });
                    }
                } else {
                    this.previewHtml = `<code>${this.latexInput}</code>`;
                    this.previewError = '';
                }
            },

            get canUndo() {
                return this._historyIndex > 0;
            },
            get canRedo() {
                return this._historyIndex < this._history.length - 1;
            },

            pushHistory() {
                this._history = this._history.slice(0, this._historyIndex + 1);
                if (this._history[this._historyIndex] === this.latexInput) return;
                this._history.push(this.latexInput);
                if (this._history.length > 120) {
                    this._history.shift();
                } else {
                    this._historyIndex++;
                }
            },

            undo() {
                if (this._historyIndex > 0) {
                    if (this._history[this._historyIndex] !== this.latexInput) {
                        this.pushHistory();
                    }
                    this._historyIndex--;
                    this.latexInput = this._history[this._historyIndex];
                    this.$nextTick(() => {
                        this.updatePreview();
                        this.autoResize();
                    });
                }
            },

            redo() {
                if (this._historyIndex < this._history.length - 1) {
                    this._historyIndex++;
                    this.latexInput = this._history[this._historyIndex];
                    this.$nextTick(() => {
                        this.updatePreview();
                        this.autoResize();
                    });
                }
            },

            autoResize() {
                const el = this.$refs.latexInput;
                if (!el) return;
                el.style.height = 'auto';
                const max = 220;
                el.style.height = `${Math.min(el.scrollHeight, max)}px`;
                el.style.overflowY = el.scrollHeight > max ? 'auto' : 'hidden';
            },

            onInput() {
                this.updatePreview();
                this.autoResize();
                clearTimeout(this._historyTimer);
                this._historyTimer = setTimeout(() => this.pushHistory(), 600);
            },

            insertAt(text) {
                const el = this.$refs.latexInput;
                this.pushHistory();
                if (!el) {
                    this.latexInput += text;
                    this.updatePreview();
                    return;
                }

                const start = el.selectionStart;
                const end = el.selectionEnd;
                this.latexInput = this.latexInput.substring(0, start) + text + this.latexInput.substring(end);
                this.$nextTick(() => {
                    const pos = start + text.length;
                    el.setSelectionRange(pos, pos);
                    el.focus();
                    this.updatePreview();
                    this.autoResize();
                });
            },

            insertSym(sym) {
                this.insertAt(sym.latex);
            },

            onTab() {
                this.insertAt('  ');
            },

            clearInput() {
                this.pushHistory();
                this.latexInput = '';
                this.previewHtml = '';
                this.previewError = '';
                this.$nextTick(() => {
                    if (this.$refs.latexInput) this.$refs.latexInput.focus();
                    this.autoResize();
                });
            },

            setMode(mode) {
                this.displayMode = mode;
                this.updatePreview();
            },

            wrap(latex) {
                const trimmed = latex.trim();
                return this.displayMode === 'display'
                    ? `\\[ ${trimmed} \\]`
                    : `\\( ${trimmed} \\)`;
            },

            copyFormula() {
                if (!this.latexInput.trim()) return;
                this.doCopy(this.wrap(this.latexInput));
            },

            quickCopy(chip) {
                this.doCopy(`\\( ${chip.latex} \\)`);
            },

            doCopy(text) {
                const onOk = () => {
                    this.copyLabel = this.i18n.copied;
                    setTimeout(() => {
                        this.copyLabel = `📋 ${this.i18n.copyFormula}`;
                    }, 2200);
                };
                const onFail = () => {
                    this.copyLabel = this.i18n.copyFailed;
                    setTimeout(() => {
                        this.copyLabel = `📋 ${this.i18n.copyFormula}`;
                    }, 2200);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(onOk).catch(() => this.legacyCopy(text, onOk, onFail));
                } else {
                    this.legacyCopy(text, onOk, onFail);
                }
            },

            legacyCopy(text, onOk, onFail) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy') ? onOk() : onFail();
                } catch (_) {
                    onFail();
                }
                document.body.removeChild(ta);
            },

            openEditor() {
                this.showEditor = true;
                this.$nextTick(() => {
                    if (this.$refs.latexInput) this.$refs.latexInput.focus();
                    this.updatePreview();
                });
            },

            closeEditor() {
                this.showEditor = false;
            },
        };
    }

    function registerMathEditorV2() {
        if (typeof Alpine === 'undefined' || Alpine.data === undefined) return;
        if (Alpine._data && Alpine._data.mathEditorV2) return;
        Alpine.data('mathEditorV2', mathEditorV2Factory);
    }

    if (window.Alpine) {
        registerMathEditorV2();
    }

    document.addEventListener('alpine:init', registerMathEditorV2);
    document.addEventListener('alpine:initialized', registerMathEditorV2);
})();
