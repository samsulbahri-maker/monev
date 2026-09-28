@extends('layouts.app')
@section('content')
    <div class="toolbar"><div><div class="eyebrow">Master data</div><h2 style="margin-bottom:0">Program</h2></div><a class="btn btn-primary" href="{{ route('programs.create') }}">+ Tambah program</a></div>
    <form class="card" method="get" style="margin-bottom:18px"><div class="field" style="margin:0"><label>Cari program</label><input name="search" value="{{ request('search') }}" placeholder="Nama program"></div><button class="btn btn-secondary" style="margin-top:14px">Cari</button></form>
    <div class="card"><table><thead><tr><th>Program</th><th>Jenis</th><th>OPD terkait</th><th>Status</th><th>Usulan</th><th></th></tr></thead><tbody>
        @forelse($programs as $program)<tr><td><strong>{{ $program->name }}</strong></td><td>{{ $program->type }}</td><td>{{ $program->opds->pluck('name')->join(', ') ?: '-' }}</td><td><span class="badge {{ $program->is_active ? '' : 'red' }}">{{ $program->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td>{{ $program->proposals_count }}</td><td><div class="actions"><a class="btn btn-secondary" href="{{ route('programs.edit', $program) }}">Edit</a><form method="post" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Hapus program ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td></tr>
        @empty<tr><td colspan="6" class="muted">Belum ada data program.</td></tr>@endforelse
    </tbody></table><div style="margin-top:18px">{{ $programs->links() }}</div></div>
@endsection