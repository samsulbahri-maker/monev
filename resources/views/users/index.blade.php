@extends('layouts.app')
@section('content')
    <div class="toolbar"><div><div class="eyebrow">Master data</div><h2 style="margin-bottom:0">User</h2></div><a class="btn btn-primary" href="{{ route('users.create') }}">+ Tambah user</a></div>
    <form class="card" method="get" style="margin-bottom:18px"><div class="field" style="margin:0"><label>Cari user</label><input name="search" value="{{ request('search') }}" placeholder="Nama atau email"></div><button class="btn btn-secondary" style="margin-top:14px">Cari</button></form>
    <div class="card"><table><thead><tr><th>Nama</th><th>Email</th><th>OPD</th><th>Peran</th><th></th></tr></thead><tbody>
        @forelse($users as $user)<tr><td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}</td><td>{{ $user->opd?->name ?? '-' }}</td><td><span class="badge">{{ $user->role }}</span></td><td><div class="actions"><a class="btn btn-secondary" href="{{ route('users.edit', $user) }}">Edit</a><form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td></tr>
        @empty<tr><td colspan="5" class="muted">Belum ada data user.</td></tr>@endforelse
    </tbody></table><div style="margin-top:18px">{{ $users->links() }}</div></div>
@endsection