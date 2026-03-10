<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>@yield('title','Admin') — {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg-1:#0f172a;--bg-2:#111827;
            --card:rgba(255,255,255,.08);--card-border:rgba(255,255,255,.14);
            --text:#f8fafc;--muted:#cbd5e1;--muted-2:rgba(203,213,225,.75);
            --input-bg:rgba(255,255,255,.06);--input-border:rgba(255,255,255,.12);
            --primary:#6366f1;--primary-2:#7c3aed;--success:#22c55e;--danger:#ef4444;
            --shadow:0 20px 60px rgba(0,0,0,.35);--radius:16px;
        }
        [data-theme="light"]{
            --bg-1:#eef2ff;--bg-2:#f8fafc;
            --card:rgba(255,255,255,.78);--card-border:rgba(15,23,42,.08);
            --text:#0f172a;--muted:#475569;--muted-2:rgba(71,85,105,.8);
            --input-bg:rgba(15,23,42,.03);--input-border:rgba(15,23,42,.08);
            --shadow:0 14px 38px rgba(2,6,23,.08);
        }
        html,body{height:100%}
        body{
            font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;
            color:var(--text);
            background:radial-gradient(900px 500px at 10% 10%,rgba(99,102,241,.18),transparent 60%),
                        radial-gradient(700px 400px at 90% 15%,rgba(59,130,246,.12),transparent 60%),
                        linear-gradient(135deg,var(--bg-1),var(--bg-2));
            min-height:100vh;transition:background .25s,color .2s;
        }
        a{color:var(--primary);text-decoration:none}
        a:hover{text-decoration:underline}

        /* Layout */
        .admin-wrap{display:flex;min-height:100vh}
        .admin-sidebar{
            width:240px;flex-shrink:0;padding:16px;
            background:var(--card);border-right:1px solid var(--card-border);
            backdrop-filter:blur(12px);display:flex;flex-direction:column;gap:8px;
        }
        .admin-sidebar .brand{
            display:flex;align-items:center;gap:10px;padding:12px;
            border:1px solid var(--card-border);border-radius:14px;
            background:rgba(255,255,255,.03);margin-bottom:8px;font-weight:800;font-size:15px;
        }
        .brand-dot{width:10px;height:10px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#3b82f6)}
        .admin-sidebar a{
            display:flex;align-items:center;gap:10px;padding:10px 12px;
            border-radius:12px;font-size:13px;font-weight:600;color:var(--muted);
            transition:.15s;border:1px solid transparent;
        }
        .admin-sidebar a:hover{background:rgba(255,255,255,.05);color:var(--text);text-decoration:none}
        .admin-sidebar a.active{
            background:rgba(99,102,241,.10);border-color:rgba(99,102,241,.25);color:var(--text);
        }
        .sidebar-bottom{margin-top:auto;padding-top:12px;border-top:1px solid var(--card-border)}
        .sidebar-bottom a{font-size:12px}

        .admin-content{flex:1;padding:24px;overflow-y:auto;min-width:0}

        /* Cards */
        .page-title{font-size:22px;font-weight:800;margin-bottom:20px}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px}
        .stat-card{
            padding:16px;border-radius:var(--radius);
            background:var(--card);border:1px solid var(--card-border);
            backdrop-filter:blur(12px);box-shadow:var(--shadow);
        }
        .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:6px}
        .stat-value{font-size:28px;font-weight:800;letter-spacing:-.02em}
        .stat-sub{font-size:11px;color:var(--muted-2);margin-top:4px}

        /* Table */
        .card{
            border-radius:var(--radius);background:var(--card);border:1px solid var(--card-border);
            backdrop-filter:blur(12px);box-shadow:var(--shadow);overflow:hidden;margin-bottom:24px;
        }
        .card-header{
            padding:14px 16px;border-bottom:1px solid var(--card-border);
            font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:space-between;gap:12px;
        }
        .card-body{padding:16px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th{text-align:left;padding:10px 12px;font-weight:700;color:var(--muted);border-bottom:1px solid var(--card-border);font-size:11px;text-transform:uppercase;letter-spacing:.04em}
        td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
        [data-theme="light"] td{border-bottom-color:rgba(15,23,42,.04)}
        tr:hover td{background:rgba(255,255,255,.03)}
        [data-theme="light"] tr:hover td{background:rgba(15,23,42,.02)}

        /* Badge */
        .badge{
            display:inline-block;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:700;
        }
        .badge-primary{background:rgba(99,102,241,.12);color:#a5b4fc;border:1px solid rgba(99,102,241,.2)}
        .badge-success{background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.2)}
        .badge-danger{background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.2)}
        [data-theme="light"] .badge-primary{color:var(--primary)}
        [data-theme="light"] .badge-success{color:#16a34a}
        [data-theme="light"] .badge-danger{color:#dc2626}

        /* Buttons */
        .btn{
            display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;
            font-size:13px;font-weight:700;cursor:pointer;border:1px solid var(--card-border);
            background:rgba(255,255,255,.04);color:var(--text);transition:.15s;
        }
        .btn:hover{background:rgba(255,255,255,.08);text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#fff;border:none;box-shadow:0 8px 20px rgba(99,102,241,.25)}

        /* Search */
        .search-form{display:flex;gap:8px;margin-bottom:16px}
        .search-input{
            flex:1;height:40px;border-radius:12px;border:1px solid var(--input-border);
            background:var(--input-bg);color:var(--text);padding:0 12px;font-size:13px;outline:none;
        }
        .search-input:focus{border-color:rgba(99,102,241,.5);box-shadow:0 0 0 3px rgba(99,102,241,.12)}
        .search-input::placeholder{color:var(--muted-2)}

        /* Pagination */
        .pagination{display:flex;gap:6px;margin-top:16px;flex-wrap:wrap}
        .pagination a,.pagination span{
            padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;
            border:1px solid var(--card-border);color:var(--muted);
        }
        .pagination a:hover{background:rgba(255,255,255,.06);text-decoration:none}
        .pagination .active span{background:rgba(99,102,241,.15);color:var(--text);border-color:rgba(99,102,241,.3)}

        /* Chart area */
        .chart-bars{display:flex;align-items:flex-end;gap:8px;height:120px;padding:12px 0}
        .chart-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px}
        .chart-bar{
            width:100%;border-radius:6px 6px 0 0;
            background:linear-gradient(180deg,var(--primary),var(--primary-2));
            min-height:2px;transition:height .3s;
        }
        .chart-label{font-size:10px;color:var(--muted-2);white-space:nowrap}
        .chart-val{font-size:10px;font-weight:700;color:var(--muted)}

        /* Theme toggle */
        .theme-btn{
            width:36px;height:36px;border-radius:10px;border:1px solid var(--card-border);
            background:rgba(255,255,255,.04);color:var(--text);cursor:pointer;
            display:grid;place-items:center;font-size:16px;transition:.15s;
        }
        .theme-btn:hover{background:rgba(255,255,255,.08)}

        /* Message bubble in conversation detail */
        .msg-row{display:flex;gap:8px;margin-bottom:10px}
        .msg-row.ai{justify-content:flex-start}
        .msg-row.user{justify-content:flex-end}
        .msg-bubble{
            max-width:70%;padding:10px 14px;border-radius:14px;font-size:13px;line-height:1.5;
            border:1px solid var(--card-border);
        }
        .msg-row.ai .msg-bubble{background:rgba(255,255,255,.05)}
        .msg-row.user .msg-bubble{background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(124,58,237,.2));border-color:rgba(99,102,241,.3)}
        .msg-role{font-size:10px;color:var(--muted-2);margin-bottom:4px;font-weight:700}
        .msg-time{font-size:10px;color:var(--muted-2);margin-top:4px}

        /* Detail info */
        .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:20px}
        .info-item .info-label{font-size:11px;color:var(--muted);font-weight:600}
        .info-item .info-value{font-size:14px;font-weight:700;margin-top:2px}

        /* Responsive */
        @media(max-width:768px){
            .admin-sidebar{display:none}
            .stats-grid{grid-template-columns:repeat(2,1fr)}
        }
    </style>
</head>
<body data-theme="dark">
    <div class="admin-wrap">
        <nav class="admin-sidebar">
            <div class="brand"><span class="brand-dot"></span> Admin Panel</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">İstifadçilər</a>
            <a href="{{ route('admin.conversations.index') }}" class="{{ request()->routeIs('admin.conversations.*') ? 'active' : '' }}">Söhbətlər</a>
            <div class="sidebar-bottom">
                <a href="{{ route('chat.index') }}">Chat-a qayıt</a>
                <form method="POST" action="{{ route('admin.logout') }}" style="margin-top:6px">
                    @csrf
                    <button type="submit" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;font-size:12px;font-weight:600;color:var(--danger);transition:.15s;font-family:inherit">Çıxış</button>
                </form>
            </div>
        </nav>

        <div class="admin-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                <h1 class="page-title" style="margin-bottom:0">@yield('title','Dashboard')</h1>
                <button class="theme-btn" id="adminThemeBtn" title="Tema">🌙</button>
            </div>
            @yield('content')
        </div>
    </div>

    <script>
        const body = document.body;
        const themeBtn = document.getElementById('adminThemeBtn');
        const saved = localStorage.getItem('admin-theme') || 'dark';
        body.setAttribute('data-theme', saved);
        themeBtn.textContent = saved === 'dark' ? '🌙' : '☀️';
        themeBtn.addEventListener('click', () => {
            const next = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', next);
            localStorage.setItem('admin-theme', next);
            themeBtn.textContent = next === 'dark' ? '🌙' : '☀️';
        });
    </script>
    @stack('scripts')
</body>
</html>
