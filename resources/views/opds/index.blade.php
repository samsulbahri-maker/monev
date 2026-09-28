@extends('layouts.app')
@section('content')
    <div class="toolbar">
        <div><div class="eyebrow">Master data</div><h2 style="margin-bottom:0">OPD</h2></div>
        <a class="btn btn-primary" href="{{ route('opds.create') }}">+ Tambah OPD</a>
    </div>
    <form class="card" method="get" style="margin-bottom:18px"><div class="grid grid-2"><div class="field" style="margin:0"><label>Cari OPD</label><input name="search" value="{{ request('search') }}" placeholder="Nama OPD"></div></div><button class="btn btn-secondary" style="margin-top:14px">Cari</button></form>
    <div class="card"><table><thead><tr><th>Nama OPD</th><th>Program</th><th>Usulan</th><th></th></tr></thead><tbody>
        @forelse($opds as $opd)<tr><td><strong>{{ $opd->name }}</strong></td><td>{{ $opd->programs_count }}</td><td>{{ $opd->proposals_count }}</td><td><div class="actions"><a class="btn btn-secondary" href="{{ route('opds.edit', $opd) }}">Edit</a><form method="post" action="{{ route('opds.destroy', $opd) }}" onsubmit="return confirm('Hapus OPD ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td></tr>
        @empty<tr><td colspan="4" class="muted">Belum ada data OPD.</td></tr>@endforelse
    </tbody></table><div style="margin-top:18px">{{ $opds->links() }}</div></div>
@endsection