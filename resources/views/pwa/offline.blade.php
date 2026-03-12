<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tidak Ada Koneksi — CAT BAPETEN</title>
    <meta name="theme-color" content="#d97706" />
    <link rel="manifest" href="/manifest.webmanifest" />
    <link rel="icon" href="/pwa/icon-192.png" type="image/png" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%);
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1c1917;
        }

        .card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.15);
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 460px;
            width: 100%;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            border-radius: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px -4px rgba(217, 119, 6, 0.4);
        }

        .logo-wrap svg {
            width: 44px;
            height: 44px;
            fill: white;
        }

        .wifi-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .wifi-icon svg {
            width: 64px;
            height: 64px;
            color: #d97706;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            font-size: 0.9rem;
            color: #78716c;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            border-radius: 9999px;
            padding: 0.25rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s;
            box-shadow: 0 4px 14px -2px rgba(217, 119, 6, 0.35);
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #f5f5f4;
            color: #44403c;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-secondary:hover {
            background: #e7e5e4;
        }

        .divider {
            height: 1px;
            background: #f5f5f4;
            margin: 1.25rem 0;
        }

        .app-name {
            font-size: 0.75rem;
            color: #a8a29e;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="logo-wrap">
            <!-- Atom / nuclear icon -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 11.5a.5.5 0 1 1 0 1 .5.5 0 0 1 0-1zm0-1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c.83 0 1.5.67 1.5 1.5S12.83 8 12 8s-1.5-.67-1.5-1.5S11.17 5 12 5zm5.66 8c0 1.1-.9 2-2 2h-7.32c-1.1 0-2-.9-2-2s.9-2 2-2h7.32c1.1 0 2 .9 2 2zm-8.32 4.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm5.32 0c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z" />
            </svg>
        </div>

        <div class="wifi-icon">
            <!-- No wifi SVG -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                stroke-linejoin="round">
                <line x1="1" y1="1" x2="23" y2="23" />
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55" />
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39" />
                <path d="M10.71 5.05A16 16 0 0 1 22.56 9" />
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88" />
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                <line x1="12" y1="20" x2="12.01" y2="20" />
            </svg>
        </div>

        <h1>Tidak ada koneksi</h1>
        <p class="subtitle">
            Tampaknya perangkat Anda sedang offline. Periksa koneksi internet Anda, lalu coba lagi untuk mengakses CAT
            BAPETEN.
        </p>

        <div class="badge">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
            </svg>
            Sesi ujian yang sedang berjalan tidak terpengaruh
        </div>

        <div class="actions">
            <button class="btn-primary" onclick="window.location.reload()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <polyline points="23 4 23 10 17 10" />
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                </svg>
                Coba Lagi
            </button>
            <a href="/admin" class="btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="divider"></div>
        <p class="app-name">CAT BAPETEN &mdash; Sistem Computer Assisted Test</p>
    </div>

    <script>
        // Auto-reload saat koneksi kembali
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>

</html>
