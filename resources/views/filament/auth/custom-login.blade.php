<x-filament-panels::page.simple>
    {{-- CUSTOM CSS: Using inline styles to guarantee layout stability without rebuilding assets --}}
    <style>
        /* [CRITICAL] Override Filament's default simple page layout constraints */
        .fi-simple-main,
        .fi-simple-page,
        main.fi-simple-main {
            max-width: none !important;
            width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            min-height: 100vh !important;
        }
        
        /* Hide default header/logo provided by Filament layout */
        .fi-simple-header {
            display: none !important;
        }

        /* Body background correction */
        body {
            background-color: #f8fafc !important; /* Slate-50 */
            margin: 0;
            padding: 0;
        }

        /* --- Main Container --- */
        .custom-login-container {
            display: flex;
            width: 100%;
            max-width: 850px; /* Further reduced width */
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin: 10px;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            position: relative;
            z-index: 10;
            /* Removed min-height to fit content better */
        }

        /* --- Left Side: Form --- */
        .login-form-side {
            flex: 1;
            padding: 2rem; /* Reduced padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #ffffff;
            min-width: 300px;
            position: relative;
            z-index: 20;
        }

        /* --- Right Side: Visuals --- */
        .login-brand-side {
            flex: 1.1;
            background-color: #0f172a; /* Slate 900 */
            color: white;
            padding: 2rem; /* Reduced padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .custom-login-container {
                flex-direction: column-reverse;
                max-width: 500px;
                min-height: auto;
            }
            .login-brand-side {
                padding: 2rem;
                min-height: 250px;
                flex: none;
            }
            .login-form-side {
                padding: 2rem;
            }
        }

        /* Element Styling */
        .brand-logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        /* Force logo sizing */
        .brand-logo-img {
            height: 40px !important; /* Smaller logo */
            width: auto !important;
            max-width: 100px;
            object-fit: contain;
        }
        
        .brand-text-group {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        
        .brand-subtitle {
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #d97706; /* Amber 600 */
            font-weight: 700;
            margin-top: 2px;
        }

        .form-heading {
            font-size: 1rem; /* Smaller heading */
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
            margin-top: 0;
        }

        .form-subheading {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 1.25rem;
            line-height: 1.4;
        }

        .footer-text {
            border-top: 1px solid #f1f5f9;
            margin-top: 1rem;
            padding-top: 0.75rem;
            font-size: 0.65rem;
            color: #94a3b8;
            line-height: 1.4;
        }

        /* Visual Side Specifics */
        .visual-heading {
            font-size: 1.5rem; /* Smaller visual heading */
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.75rem;
            margin-top: 0;
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .visual-highlight {
            background-image: linear-gradient(to right, #fbbf24, #fcd34d);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #fbbf24; /* Fallback */
        }

        .visual-desc {
            color: #cbd5e1;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            backdrop-filter: blur(4px);
            width: fit-content;
        }

        /* Decorative Elements */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            z-index: 1;
            pointer-events: none;
        }
        .deco-1 { top: -20%; right: -20%; width: 400px; height: 400px; background: #fbbf24; }
        .deco-2 { bottom: -20%; left: -20%; width: 400px; height: 400px; background: #3b82f6; }
        
        /* Grid Pattern */
        .grid-bg {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 32px 32px;
            z-index: 0;
        }
    </style>

    <div class="custom-login-container">
        {{-- LEFT SIDE --}}
        <div class="login-form-side">
            {{-- Header --}}
            <div class="brand-logo-container">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo BAPETEN" class="brand-logo-img">
                <div class="brand-text-group">
                    <span class="brand-title">BAPETEN</span>
                    <span class="brand-subtitle">Computer Assisted Test</span>
                </div>
            </div>

            {{-- Title --}}
            <div>
                <h2 class="form-heading">Login</h2>
                <p class="form-subheading">Masuk untuk mengakses panel pengelolaan sistem CAT.</p>
            </div>

            {{-- Filament Form --}}
            <div style="margin-bottom: 1rem;">
                {{ $this->content }}
            </div>

            {{-- Footer --}}
            <div class="footer-text">
                <div>&copy; {{ date('Y') }} Badan Pengawas Tenaga Nuklir.</div>
                <div style="margin-top: 2px;"></div>
            </div>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="login-brand-side">
            {{-- Backgrounds --}}
            <div class="grid-bg"></div>
            <div class="deco-circle deco-1"></div>
            <div class="deco-circle deco-2"></div>

            {{-- Content --}}
            <div style="position: relative; z-index: 5;">
                <div style="width: 50px; height: 5px; background: #f59e0b; margin-bottom: 2rem; border-radius: 4px;"></div>
                
                <h3 class="visual-heading">
                    Integritas, Kualitas, <br>
                    <span class="visual-highlight">& Keamanan Ujian</span>
                </h3>
    
                <div class="visual-desc">
                    <p style="margin: 0 0 1rem 0;">
                        Platform Computer Assisted Test (CAT) resmi BAPETEN untuk memastikan proses seleksi yang <strong>transparan</strong>, <strong>akuntabel</strong>, dan <strong>bebas intervensi</strong>.
                    </p>
                    <p style="margin: 1.5rem 0 0 0; font-style: italic; color: #94a3b8; border-left: 3px solid rgba(245, 158, 11, 0.5); padding-left: 1rem; font-size: 0.9em;">
                        "Menjamin standar kompetensi tinggi melalui sistem evaluasi terpercaya."
                    </p>
                </div>
    
                <div class="secure-badge">
                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: white;">Secure Access Area</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; line-height: 1;">Authorized personnel only</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
