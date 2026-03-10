<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Admin Giriş — {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg-1:#0f172a;--bg-2:#111827;
            --card:rgba(255,255,255,.08);--card-border:rgba(255,255,255,.14);
            --text:#f8fafc;--muted:#cbd5e1;
            --input-bg:rgba(255,255,255,.06);--input-border:rgba(255,255,255,.12);
            --primary:#6366f1;--primary-hover:#5558ea;
            --danger:#ef4444;--success:#22c55e;
            --shadow:0 20px 60px rgba(0,0,0,.35);--radius:22px;
        }
        [data-theme="light"]{
            --bg-1:#eef2ff;--bg-2:#f8fafc;
            --card:rgba(255,255,255,.78);--card-border:rgba(15,23,42,.08);
            --text:#0f172a;--muted:#475569;
            --input-bg:rgba(15,23,42,.03);--input-border:rgba(15,23,42,.08);
            --shadow:0 14px 38px rgba(2,6,23,.08);
        }
        body{
            min-height:100vh;
            font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;
            color:var(--text);
            background:
                radial-gradient(900px 500px at 10% 10%,rgba(99,102,241,.25),transparent 60%),
                radial-gradient(700px 400px at 90% 15%,rgba(59,130,246,.18),transparent 60%),
                radial-gradient(800px 500px at 50% 100%,rgba(168,85,247,.18),transparent 60%),
                linear-gradient(135deg,var(--bg-1),var(--bg-2));
            display:grid;place-items:center;padding:24px;
            transition:background .25s,color .2s;
        }
        .login-shell{width:100%;max-width:420px}
        .login-card{
            background:var(--card);border:1px solid var(--card-border);
            border-radius:var(--radius);backdrop-filter:blur(12px);
            -webkit-backdrop-filter:blur(12px);box-shadow:var(--shadow);
            padding:34px;
        }
        .brand{
            display:inline-flex;align-items:center;gap:10px;
            padding:10px 14px;border:1px solid var(--card-border);
            border-radius:999px;width:fit-content;
            background:rgba(255,255,255,.03);margin-bottom:24px;
        }
        .brand-dot{
            width:10px;height:10px;border-radius:999px;
            background:linear-gradient(135deg,#ef4444,#f97316);
            box-shadow:0 0 0 6px rgba(239,68,68,.12);
        }
        .brand span{font-size:14px;color:var(--muted);letter-spacing:.2px}
        .login-header{margin-bottom:20px}
        .login-header h2{font-size:28px;font-weight:800;margin-bottom:6px;letter-spacing:-.02em}
        .login-header p{color:var(--muted);font-size:14px}
        .alerts{display:grid;gap:10px;margin-bottom:16px}
        .alert{border-radius:12px;padding:12px 14px;font-size:14px;border:1px solid transparent}
        .alert.error{background:rgba(239,68,68,.10);border-color:rgba(239,68,68,.25);color:#fca5a5}
        [data-theme="light"] .alert.error{color:#dc2626}
        form{display:grid;gap:14px}
        .field{display:grid;gap:7px}
        .field label{font-size:13px;color:var(--muted);font-weight:600}
        .input-wrap input{
            width:100%;height:48px;border-radius:12px;
            border:1px solid var(--input-border);background:var(--input-bg);
            color:var(--text);padding:0 14px;outline:none;
            transition:.18s ease;font-size:14px;
        }
        .input-wrap input::placeholder{color:rgba(203,213,225,.70)}
        [data-theme="light"] .input-wrap input::placeholder{color:rgba(71,85,105,.5)}
        .input-wrap input:focus{
            border-color:rgba(99,102,241,.55);
            box-shadow:0 0 0 4px rgba(99,102,241,.16);
            background:rgba(255,255,255,.08);
        }
        [data-theme="light"] .input-wrap input:focus{background:rgba(255,255,255,.9)}
        .field-error{color:#fca5a5;font-size:12px}
        [data-theme="light"] .field-error{color:#dc2626}
        .row-between{
            display:flex;justify-content:space-between;align-items:center;
            gap:10px;margin-top:2px;margin-bottom:2px;
        }
        .remember{
            display:inline-flex;align-items:center;gap:8px;
            color:var(--muted);font-size:13px;user-select:none;
        }
        .remember input{width:15px;height:15px;accent-color:var(--primary)}
        .btn{
            border:0;outline:0;height:48px;border-radius:12px;cursor:pointer;
            font-weight:700;transition:.18s ease;display:inline-flex;
            align-items:center;justify-content:center;gap:10px;
            width:100%;font-size:14px;
        }
        .btn-primary{
            background:linear-gradient(135deg,var(--primary),#7c3aed);
            color:white;box-shadow:0 10px 30px rgba(99,102,241,.28);
        }
        .btn-primary:hover{
            transform:translateY(-1px);
            box-shadow:0 14px 34px rgba(99,102,241,.34);
        }
        .theme-toggle{
            position:fixed;top:20px;right:20px;
            width:36px;height:36px;border-radius:10px;
            border:1px solid var(--card-border);background:var(--card);
            color:var(--text);cursor:pointer;display:grid;place-items:center;
            font-size:16px;backdrop-filter:blur(12px);transition:.15s;
        }
        .theme-toggle:hover{background:rgba(255,255,255,.12)}
        .back-link{
            display:block;text-align:center;margin-top:16px;
            color:var(--muted);font-size:13px;text-decoration:none;
        }
        .back-link:hover{color:var(--text)}
        @media(max-width:520px){
            body{padding:14px}
            .login-card{padding:24px}
            .login-header h2{font-size:24px}
            .btn,.input-wrap input{height:46px}
        }
    </style>
</head>
<body data-theme="dark">
    <button class="theme-toggle" id="themeBtn" title="Tema">🌙</button>

    <div class="login-shell">
        <div class="login-card">
            <div class="brand">
                <span class="brand-dot"></span>
                <span>{{ config('app.name') }} Admin</span>
            </div>

            <div class="login-header">
                <h2>Admin Giriş</h2>
                <p>Admin panelinə daxil olmaq üçün məlumatlarını yaz.</p>
            </div>

            <div class="alerts">
                @if ($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="admin@example.com" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Şifrə</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="row-between">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Məni xatırla</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Daxil ol</button>
            </form>

            <a href="/" class="back-link">Sayta qayıt</a>
        </div>
    </div>

    <script>
        const body = document.body;
        const btn = document.getElementById('themeBtn');
        const saved = localStorage.getItem('admin-theme') || 'dark';
        body.setAttribute('data-theme', saved);
        btn.textContent = saved === 'dark' ? '🌙' : '☀️';
        btn.addEventListener('click', () => {
            const next = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', next);
            localStorage.setItem('admin-theme', next);
            btn.textContent = next === 'dark' ? '🌙' : '☀️';
        });
    </script>
</body>
</html>
