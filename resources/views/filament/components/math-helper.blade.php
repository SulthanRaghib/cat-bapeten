<div x-data="mathHelper"
    style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 16px; border-radius: 12px; border: 1px solid #f59e0b; margin-bottom: 1rem;">
    <div style="display: flex; align-items: flex-start; gap: 12px;">
        <div style="background: #f59e0b; color: white; padding: 8px; border-radius: 8px; flex-shrink: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div style="flex: 1;">
            <h4 style="margin: 0 0 8px 0; font-weight: 700; color: #92400e; font-size: 14px;">📝 Panduan Editor Soal
            </h4>
            <div
                style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 12px; color: #78350f;">
                <div>📷 <strong>Gambar:</strong> Klik 📎 → pilih file</div>
                <div>🔗 <strong>Link:</strong> Klik icon link</div>
                <div>📋 <strong>List:</strong> Bullet atau Numbered</div>
                <div>🎨 <strong>Format:</strong> Bold, Italic, dll</div>
            </div>

            <!-- Quick Chips Section -->
            <div
                style="margin-top: 12px; padding: 12px; background: white; border-radius: 8px; border: 1px dashed #d97706;">
                <p style="margin: 0 0 8px 0; font-weight: 600; color: #92400e; font-size: 12px;">📐 Rumus Matematika
                    (Klik untuk salin):</p>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="latex-chips-container">
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\frac{a}{b} \\)')">
                        <span class="chip-preview">ᵃ⁄ᵦ</span>
                        <span class="chip-label">Pecahan</span>
                    </button>
                    <button type="button" class="latex-chip-btn" x-on:click.stop.prevent="copyLatex('\\( x^{2} \\)')">
                        <span class="chip-preview">x²</span>
                        <span class="chip-label">Pangkat</span>
                    </button>
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\sqrt{x} \\)')">
                        <span class="chip-preview">√x</span>
                        <span class="chip-label">Akar</span>
                    </button>
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\sum_{i=1}^{n} x_i \\)')">
                        <span class="chip-preview">Σ</span>
                        <span class="chip-label">Sigma</span>
                    </button>
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\int_{a}^{b} f(x) dx \\)')">
                        <span class="chip-preview">∫</span>
                        <span class="chip-label">Integral</span>
                    </button>
                    <button type="button" class="latex-chip-btn" x-on:click.stop.prevent="copyLatex('\\( \\pi \\)')">
                        <span class="chip-preview">π</span>
                        <span class="chip-label">Pi</span>
                    </button>
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\alpha \\)')">
                        <span class="chip-preview">α</span>
                        <span class="chip-label">Alpha</span>
                    </button>
                    <button type="button" class="latex-chip-btn"
                        x-on:click.stop.prevent="copyLatex('\\( \\lambda \\)')">
                        <span class="chip-preview">λ</span>
                        <span class="chip-label">Lambda</span>
                    </button>
                </div>

                <!-- Math Picker Tool -->
                <div style="margin-top: 12px; border-top: 1px dashed #d97706; padding-top: 12px;">
                    <button type="button" x-on:click.stop.prevent="openPicker()" class="math-formula-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.871 4A17.926 17.926 0 003 12c0 2.874.673 5.59 1.871 8m14.13 0a17.926 17.926 0 001.87-8c0-2.874-.673-5.59-1.87-8M9 9h1.246a1 1 0 01.961.725l1.586 5.55a1 1 0 00.961.725H15m-6 4h6" />
                        </svg>
                        🔍 Cari Rumus Lainnya
                    </button>

                    <template x-if="showPicker">
                        <div class="math-picker-modal" x-on:click.self.stop.prevent="closePicker()">
                            <div class="math-picker-content">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                    <h3 style="margin: 0; font-size: 18px; font-weight: 700;">📐 Pilih Template Rumus
                                    </h3>
                                    <button type="button" x-on:click.stop.prevent="closePicker()"
                                        style="background: #f3f4f6; border: none; padding: 8px; border-radius: 8px; cursor: pointer;">✕</button>
                                </div>

                                <div style="margin-bottom: 16px;">
                                    <label style="font-size: 12px; font-weight: 600; color: #6b7280;">Kategori:</label>
                                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                                        <template x-for="cat in categories" :key="cat.id">
                                            <button type="button" x-on:click.stop.prevent="activeCategory = cat.id"
                                                class="category-btn" :class="activeCategory === cat.id ? 'active' : ''"
                                                x-text="cat.name">
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="math-picker-grid">
                                    <template x-for="formula in filteredFormulas" :key="formula.latex">
                                        <div class="math-picker-item"
                                            x-on:click.stop.prevent="copyLatex(formula.latex); closePicker()">
                                            <div class="formula" x-html="formula.preview"></div>
                                            <div class="label" x-text="formula.name"></div>
                                        </div>
                                    </template>
                                </div>

                                <div
                                    style="margin-top: 16px; padding: 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #86efac;">
                                    <p style="margin: 0; font-size: 12px; color: #166534;">
                                        <strong>💡 Tips:</strong> Klik rumus untuk menyalin ke clipboard. Lalu paste
                                        (Ctrl+V) di editor teks.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <p
                    style="margin: 10px 0 0 0; font-size: 11px; color: #78350f; display: flex; align-items: center; gap: 4px;">
                    <span
                        style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">TIP</span>
                    Klik chip atau tombol cari → paste di editor.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mathHelper', () => ({
            showPicker: false,
            activeCategory: 'basic',

            init() {
                console.log('Math Helper Component Initialized');
            },

            categories: [{
                    id: "basic",
                    name: "🔢 Dasar"
                },
                {
                    id: "fraction",
                    name: "➗ Pecahan"
                },
                {
                    id: "power",
                    name: "📈 Pangkat & Akar"
                },
                {
                    id: "greek",
                    name: "🇬🇷 Greek"
                },
                {
                    id: "calculus",
                    name: "∫ Kalkulus"
                },
                {
                    id: "physics",
                    name: "⚛️ Fisika"
                }
            ],

            formulas: [{
                    category: "basic",
                    name: "Plus Minus",
                    latex: "\\( \\pm \\)",
                    preview: "±"
                },
                {
                    category: "basic",
                    name: "Kali",
                    latex: "\\( \\times \\)",
                    preview: "×"
                },
                {
                    category: "basic",
                    name: "Bagi",
                    latex: "\\( \\div \\)",
                    preview: "÷"
                },
                {
                    category: "basic",
                    name: "Tidak sama",
                    latex: "\\( \\neq \\)",
                    preview: "≠"
                },
                {
                    category: "basic",
                    name: "Kurang dari sama",
                    latex: "\\( \\leq \\)",
                    preview: "≤"
                },
                {
                    category: "basic",
                    name: "Lebih dari sama",
                    latex: "\\( \\geq \\)",
                    preview: "≥"
                },
                {
                    category: "basic",
                    name: "Tak hingga",
                    latex: "\\( \\infty \\)",
                    preview: "∞"
                },
                {
                    category: "basic",
                    name: "Derajat",
                    latex: "\\( ^{\\circ} \\)",
                    preview: "°"
                },
                {
                    category: "fraction",
                    name: "Pecahan a/b",
                    latex: "\\( \\frac{a}{b} \\)",
                    preview: "a/b"
                },
                {
                    category: "fraction",
                    name: "Pecahan 1/2",
                    latex: "\\( \\frac{1}{2} \\)",
                    preview: "½"
                },
                {
                    category: "fraction",
                    name: "Pecahan x/y",
                    latex: "\\( \\frac{x}{y} \\)",
                    preview: "x/y"
                },
                {
                    category: "fraction",
                    name: "Persentase",
                    latex: "\\( \\frac{N_f}{N_i} \\times 100\\% \\)",
                    preview: "(Nf/Ni)×100%"
                },
                {
                    category: "power",
                    name: "Kuadrat x²",
                    latex: "\\( x^{2} \\)",
                    preview: "x²"
                },
                {
                    category: "power",
                    name: "Pangkat n",
                    latex: "\\( x^{n} \\)",
                    preview: "xⁿ"
                },
                {
                    category: "power",
                    name: "Akar kuadrat",
                    latex: "\\( \\sqrt{x} \\)",
                    preview: "√x"
                },
                {
                    category: "power",
                    name: "Akar pangkat n",
                    latex: "\\( \\sqrt[n]{x} \\)",
                    preview: "ⁿ√x"
                },
                {
                    category: "power",
                    name: "Subscript",
                    latex: "\\( x_{1} \\)",
                    preview: "x₁"
                },
                {
                    category: "power",
                    name: "Eksponen e",
                    latex: "\\( e^{x} \\)",
                    preview: "eˣ"
                },
                {
                    category: "power",
                    name: "Logaritma",
                    latex: "\\( \\log_{10} x \\)",
                    preview: "log₁₀x"
                },
                {
                    category: "power",
                    name: "Natural log",
                    latex: "\\( \\ln x \\)",
                    preview: "ln x"
                },
                {
                    category: "greek",
                    name: "Alpha",
                    latex: "\\( \\alpha \\)",
                    preview: "α"
                },
                {
                    category: "greek",
                    name: "Beta",
                    latex: "\\( \\beta \\)",
                    preview: "β"
                },
                {
                    category: "greek",
                    name: "Gamma",
                    latex: "\\( \\gamma \\)",
                    preview: "γ"
                },
                {
                    category: "greek",
                    name: "Delta",
                    latex: "\\( \\Delta \\)",
                    preview: "Δ"
                },
                {
                    category: "greek",
                    name: "Theta",
                    latex: "\\( \\theta \\)",
                    preview: "θ"
                },
                {
                    category: "greek",
                    name: "Lambda",
                    latex: "\\( \\lambda \\)",
                    preview: "λ"
                },
                {
                    category: "greek",
                    name: "Mu",
                    latex: "\\( \\mu \\)",
                    preview: "μ"
                },
                {
                    category: "greek",
                    name: "Pi",
                    latex: "\\( \\pi \\)",
                    preview: "π"
                },
                {
                    category: "greek",
                    name: "Sigma",
                    latex: "\\( \\sigma \\)",
                    preview: "σ"
                },
                {
                    category: "greek",
                    name: "Omega",
                    latex: "\\( \\omega \\)",
                    preview: "ω"
                },
                {
                    category: "calculus",
                    name: "Integral",
                    latex: "\\( \\int_{a}^{b} f(x) dx \\)",
                    preview: "∫ᵃᵇ f(x)dx"
                },
                {
                    category: "calculus",
                    name: "Sigma Sum",
                    latex: "\\( \\sum_{i=1}^{n} x_i \\)",
                    preview: "Σxᵢ"
                },
                {
                    category: "calculus",
                    name: "Limit",
                    latex: "\\( \\lim_{x \\to \\infty} \\)",
                    preview: "lim x→∞"
                },
                {
                    category: "calculus",
                    name: "Turunan",
                    latex: "\\( \\frac{dy}{dx} \\)",
                    preview: "dy/dx"
                },
                {
                    category: "calculus",
                    name: "Turunan parsial",
                    latex: "\\( \\frac{\\partial y}{\\partial x} \\)",
                    preview: "∂y/∂x"
                },
                {
                    category: "physics",
                    name: "Dosis Radiasi",
                    latex: "\\( D = \\frac{E}{m} \\)",
                    preview: "D = E/m"
                },
                {
                    category: "physics",
                    name: "Aktivitas",
                    latex: "\\( A = \\lambda N \\)",
                    preview: "A = λN"
                },
                {
                    category: "physics",
                    name: "Peluruhan",
                    latex: "\\( N(t) = N_0 e^{-\\lambda t} \\)",
                    preview: "N(t)=N₀e⁻λt"
                },
                {
                    category: "physics",
                    name: "Half-life",
                    latex: "\\( t_{1/2} = \\frac{\\ln 2}{\\lambda} \\)",
                    preview: "t½=ln2/λ"
                },
                {
                    category: "physics",
                    name: "E=mc²",
                    latex: "\\( E = mc^{2} \\)",
                    preview: "E=mc²"
                },
                {
                    category: "physics",
                    name: "Hukum Invers",
                    latex: "\\( I = \\frac{I_0}{r^2} \\)",
                    preview: "I=I₀/r²"
                }
            ],

            get filteredFormulas() {
                return this.formulas.filter(f => f.category === this.activeCategory);
            },

            openPicker() {
                console.log('openPicker called');
                this.showPicker = true;
            },

            closePicker() {
                console.log('closePicker called');
                this.showPicker = false;
            },

            copyLatex(text) {
                console.log('copyLatex called with:', text);

                if (!text) {
                    console.error('No text provided');
                    return;
                }

                // Create textarea to copy
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.cssText = "position: fixed; left: -9999px; top: 0px;";
                document.body.appendChild(textArea);
                textArea.select();

                let success = false;
                try {
                    success = document.execCommand('copy');
                    console.log('Copy success:', success);
                } catch (err) {
                    console.error('Copy error:', err);
                }

                document.body.removeChild(textArea);

                if (success) {
                    this.notify("✅ Rumus disalin! Paste (Ctrl+V) di editor.");
                } else {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(() => {
                            console.log('Modern API success');
                            this.notify("✅ Rumus disalin! Paste (Ctrl+V) di editor.");
                        }).catch(err => {
                            console.error('Modern API failed:', err);
                            this.notify("❌ Gagal menyalin.");
                        });
                    } else {
                        this.notify("❌ Gagal menyalin.");
                    }
                }
            },

            notify(message) {
                const existing = document.getElementById('math-helper-notification');
                if (existing) existing.remove();

                const notification = document.createElement("div");
                notification.id = 'math-helper-notification';
                notification.innerHTML = message;
                notification.style.cssText =
                    "position: fixed; bottom: 20px; right: 20px; background: " +
                    (message.includes('✅') ? '#10b981' : '#ef4444') +
                    "; color: white; padding: 12px 20px; border-radius: 8px; z-index: 100000; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);";

                document.body.appendChild(notification);

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.opacity = '0';
                        notification.style.transition = 'opacity 0.3s';
                        setTimeout(() => {
                            if (notification.parentNode) notification.remove();
                        }, 300);
                    }
                }, 3000);
            }
        }));
    });
</script>

<style>
    .latex-chip-btn {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 12px;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 2px solid #fcd34d;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        min-width: 60px;
        font-family: inherit;
    }

    .latex-chip-btn:hover {
        background: linear-gradient(135deg, #fde68a, #fbbf24);
        border-color: #f59e0b;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .latex-chip-btn:active {
        transform: translateY(0);
    }

    .math-formula-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #fffbeb;
        border: 1px solid #f59e0b;
        border-radius: 8px;
        color: #92400e;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        justify-content: center;
    }

    .math-formula-btn:hover {
        background: #fef3c7;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);
    }

    .math-picker-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        backdrop-filter: blur(2px);
    }

    .math-picker-content {
        background: white;
        padding: 24px;
        border-radius: 16px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .math-picker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
    }

    .math-picker-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .math-picker-item:hover {
        border-color: #f59e0b;
        background: #fffbeb;
        transform: translateY(-2px);
    }

    .math-picker-item .formula {
        font-family: 'Times New Roman', Times, serif;
        font-size: 16px;
        margin-bottom: 4px;
        color: #1f2937;
    }

    .math-picker-item .label {
        font-size: 10px;
        color: #6b7280;
    }

    .latex-chip-btn .chip-preview {
        font-size: 18px;
        font-weight: 500;
        color: #92400e;
        line-height: 1.2;
    }

    .latex-chip-btn .chip-label {
        font-size: 9px;
        color: #b45309;
        margin-top: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .category-btn {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        border: 2px solid #e5e7eb;
        background: white;
        transition: all 0.2s ease;
    }

    .category-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .category-btn.active {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }
</style>
