<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | Akses Ditolak - CAT BAPETEN</title>
    <style>
        :root {
            --bg: #f7f8fb;
            --card: #ffffff;
            --text: #1d2433;
            --muted: #5d6980;
            --accent: #f5b800;
            --accent-dark: #d59d00;
            --line: #e6e9f0;
            --ring: rgba(245, 184, 0, 0.28);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", "Segoe UI", Tahoma, sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 500px at -10% -20%, #fff3cc 0%, transparent 65%),
                radial-gradient(800px 460px at 120% 120%, #ffe9a5 0%, transparent 70%),
                var(--bg);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .panel {
            width: min(720px, 100%);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(24, 33, 52, 0.08);
            overflow: hidden;
        }

        .brand {
            padding: 18px 22px;
            border-bottom: 1px solid var(--line);
            font-weight: 700;
            letter-spacing: 0.03em;
            font-size: 14px;
        }

        .content {
            padding: 34px 26px 28px;
            text-align: center;
        }

        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 84px;
            border-radius: 999px;
            background: #fff8e1;
            color: #946e00;
            border: 1px solid #ffe3a1;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0;
            font-size: clamp(24px, 4vw, 34px);
            line-height: 1.2;
        }

        p {
            margin: 12px auto 0;
            color: var(--muted);
            max-width: 560px;
            line-height: 1.6;
            font-size: 15px;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 120ms ease, box-shadow 120ms ease, background-color 120ms ease;
        }

        .btn-primary {
            background: var(--accent);
            color: #2b2100;
            box-shadow: 0 10px 20px var(--ring);
        }

        .btn-primary:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #ffffff;
            color: #2f3c54;
            border: 1px solid var(--line);
        }

        .btn-secondary:hover {
            background: #f9fafc;
            transform: translateY(-1px);
        }

        .hint {
            margin-top: 18px;
            font-size: 12px;
            color: #7a8599;
        }

        @media (max-width: 540px) {
            .content {
                padding: 28px 18px 22px;
            }

            .actions {
                gap: 10px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    @php
        $previous = url()->previous();
        $current = url()->current();

        if (!is_string($previous) || $previous === $current) {
            $previous = request()->is('admin*') ? url('/admin') : url('/');
        }
    @endphp

    <div class="panel">
        <div class="brand">CAT BAPETEN</div>

        <div class="content">
            <div class="code">403</div>
            <h1>Akses Ditolak</h1>
            <p>
                Maaf, Anda tidak memiliki izin untuk membuka halaman ini.
                Silakan kembali ke halaman sebelumnya atau ke dashboard.
            </p>

            <div class="actions">
                <a class="btn btn-primary" href="{{ $previous }}">Kembali ke Halaman Sebelumnya</a>
                <a class="btn btn-secondary" href="{{ request()->is('admin*') ? url('/admin') : url('/') }}">Ke
                    Dashboard</a>
            </div>

            <div class="hint">
                Jika Anda merasa ini seharusnya bisa diakses, hubungi admin sistem.
            </div>
        </div>
    </div>
</body>

</html>
