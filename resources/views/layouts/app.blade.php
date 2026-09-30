<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Monev KDH/WKDH' }}</title>
    <style>
        :root { --ink:#19324a; --muted:#6d7d8d; --line:#dce5ea; --paper:#f5f8f7; --white:#fff; --teal:#0b8277; --orange:#e08a3e; --red:#bd4f56; }
        * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:var(--paper); font:14px/1.5 Arial,sans-serif; }
        a { color:inherit; text-decoration:none; } .shell { display:flex; min-height:100vh; }
        .sidebar { width:245px; background:#173b4d; color:#dcecee; padding:24px 16px; flex:none; }
        .brand { display:flex; gap:11px; align-items:center; padding:4px 10px 28px; } .brand-mark { width:37px; height:37px; border-radius:10px; background:#d6a65b; color:#173b4d; display:grid; place-items:center; font-weight:800; }
        .brand strong { display:block; font-size:16px; letter-spacing:.3px; } .brand small { color:#9fc5c7; }
        .nav-label { color:#83aeb2; text-transform:uppercase; letter-spacing:1.4px; font-size:10px; padding:14px 12px 7px; }
        .nav-link { display:block; padding:10px 12px; margin:3px 0; border-radius:7px; color:#cfe3e5; } .nav-link:hover,.nav-link.active { background:#24566a; color:#fff; }
        .main { flex:1; min-width:0; } .topbar { min-height:70px; padding:16px 34px; background:var(--white); border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; gap:20px; }
        .topbar h1 { margin:0; font-size:20px; } .user-chip { display:flex; align-items:center; gap:10px; color:var(--muted); } .avatar { background:#d9ece9; color:#17645e; border-radius:50%; width:34px; height:34px; display:grid; place-items:center; font-weight:bold; }
        .content { max-width:1400px; padding:30px 34px 50px; margin:auto; } .eyebrow { color:var(--teal); text-transform:uppercase; letter-spacing:1.5px; font-weight:bold; font-size:11px; } h2 { margin:5px 0 22px; font-size:29px; } h3 { margin:0 0 12px; font-size:17px; }
        .grid { display:grid; gap:16px; } .grid-4 { grid-template-columns:repeat(4,1fr); } .grid-2 { grid-template-columns:repeat(2,1fr); } .card { background:var(--white); border:1px solid var(--line); border-radius:9px; padding:20px; box-shadow:0 4px 14px #183b4d09; }
        .stat { border-top:4px solid var(--teal); } .stat:nth-child(2){border-color:var(--orange)} .stat:nth-child(3){border-color:#5d88a8} .stat:nth-child(4){border-color:#789b62} .stat-label { color:var(--muted); font-size:12px; } .stat-value { font-size:28px; font-weight:700; margin-top:5px; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap; } .actions { display:flex; gap:9px; align-items:center; flex-wrap:wrap; }
        .btn { display:inline-block; border:0; border-radius:6px; padding:9px 14px; cursor:pointer; font-weight:700; font-size:13px; } .btn-primary { background:var(--teal); color:#fff; } .btn-secondary { background:#eaf1f2; color:var(--ink); } .btn-danger { background:#f8e8e8; color:var(--red); } .btn:hover { filter:brightness(.96); }
        input,select,textarea { width:100%; padding:10px 11px; border:1px solid #cbd8dc; border-radius:5px; background:#fff; color:var(--ink); font:inherit; } textarea { min-height:100px; resize:vertical; } label { display:block; font-weight:bold; font-size:12px; margin:0 0 6px; } .field { margin-bottom:15px; } .help { font-size:12px; color:var(--muted); }
        table { width:100%; border-collapse:collapse; } th { text-align:left; color:var(--muted); font-size:11px; text-transform:uppercase; letter-spacing:.7px; background:#f7faf9; } th,td { padding:12px 10px; border-bottom:1px solid var(--line); vertical-align:top; } tbody tr:hover { background:#fbfdfc; }
        .badge { display:inline-block; padding:4px 8px; border-radius:20px; background:#e9f3f1; color:#17645e; font-size:11px; font-weight:bold; } .badge.orange { background:#fff0df; color:#a95e17; } .badge.red { background:#fceaea; color:#a7464c; }
        .progress { height:7px; border-radius:10px; background:#e4eceb; overflow:hidden; min-width:90px; } .progress > span { display:block; height:100%; background:var(--teal); } .muted { color:var(--muted); } .flash { background:#e4f4ef; color:#126257; border:1px solid #bde2d8; padding:11px 14px; border-radius:6px; margin-bottom:18px; }
        .pagination { display:flex; gap:4px; align-items:center; list-style:none; padding:0; margin:0; } .pagination .page-item { margin:0; } .pagination .page-link { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 10px; border:1px solid var(--line); border-radius:5px; background:var(--white); color:var(--ink); font-weight:700; } .pagination .page-link:hover { background:#eaf1f2; } .pagination .page-item.active .page-link { background:var(--teal); border-color:var(--teal); color:#fff; } .pagination .page-item.disabled .page-link { color:#a4b1b7; background:#f7faf9; cursor:not-allowed; }
        .login-page { min-height:100vh; display:grid; place-items:center; background:linear-gradient(135deg,#173b4d,#0b8277); } .login-box { background:#fff; width:min(420px,calc(100% - 32px)); padding:34px; border-radius:12px; box-shadow:0 18px 60px #092a3860; } .login-box h1 { margin:0 0 5px; } .login-box p { color:var(--muted); margin:0 0 25px; }
        .detail-header { display:flex; justify-content:space-between; gap:20px; align-items:flex-start; margin-bottom:20px; } .detail-title { font-size:25px; margin:5px 0 7px; } .kpi { font-size:25px; font-weight:bold; color:var(--teal); } .checklist { display:flex; flex-wrap:wrap; gap:8px; }
        @media(max-width:950px){ .sidebar{width:205px}.grid-4{grid-template-columns:repeat(2,1fr)}.content{padding:24px 20px} .topbar{padding:14px 20px} }
        @media(max-width:650px){ .shell{display:block}.sidebar{width:100%;padding:13px 12px}.brand{padding:3px 8px 12px}.nav-label{display:none}.nav-link{display:inline-block;margin:0 2px;padding:7px 9px}.topbar{align-items:flex-start}.topbar h1{font-size:17px}.content{padding:20px 14px}.grid-4,.grid-2{grid-template-columns:1fr}.card{padding:15px;overflow:auto}.detail-header{display:block}.hide-mobile{display:none} table{min-width:760px} }
    </style>
</head>
<body>
@if(auth()->check())
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">M</span><span><strong>Monev</strong><small>KDH / WKDH</small></span></a>
        <div class="nav-label">Workspace</div>
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Ringkasan</a>
        <a class="nav-link {{ request()->routeIs('proposals.*') ? 'active' : '' }}" href="{{ route('proposals.index') }}">Usulan kegiatan</a>
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Laporan progres</a>
        @can('manage-master-data')
            <div class="nav-label">Master</div>
            <a class="nav-link {{ request()->routeIs('opds.*') ? 'active' : '' }}" href="{{ route('opds.index') }}">OPD</a>
            <a class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}" href="{{ route('programs.index') }}">Program</a>
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">User</a>
        @endcan
        <div class="nav-label">Akun</div>
        <form method="post" action="{{ route('logout') }}"><button class="nav-link" style="background:none;border:0;width:100%;text-align:left;cursor:pointer;color:#cfe3e5">Keluar</button>@csrf</form>
    </aside>
    <main class="main">
        <header class="topbar"><h1>{{ $title ?? 'Ringkasan' }}</h1><div class="user-chip"><span>{{ auth()->user()->name }}</span><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span></div></header>
        <div class="content">
            @if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="flash" style="background:#fceaea;color:#a7464c;border-color:#efc3c3">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>
    </main>
</div>
@else
    @yield('guest')
@endif
</body>
</html>
