@extends('layouts.app')
@section('content')
    <div class="toolbar">
        <div>
            <div class="eyebrow">Data pelaksanaan</div>
            <h2 style="margin-bottom:0">Usulan kegiatan</h2>
        </div><a class="btn btn-primary" href="{{ route('proposals.create') }}">+ Tambah usulan</a>
    </div>
    <form class="card" method="get" style="margin-bottom:18px">
        <div class="grid grid-4">
            <div class="field" style="margin:0"><label>Cari program / lokasi</label><input name="search"
                    value="{{ request('search') }}" placeholder="Ketik kata kunci"></div>
            <div class="field" style="margin:0"><label>Tahun</label><input type="number" name="year"
                    value="{{ request('year') }}" placeholder="2026"></div>
            <div class="field" style="margin:0"><label>OPD</label><select name="opd_id">
                    <option value="">Semua OPD</option>@foreach($opds as $opd)
                        <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->name }}</option>
                    @endforeach
                </select></div>
            <div class="field" style="margin:0"><label>Status</label><select name="status">
                    <option value="">Semua status</option>@foreach($statuses as $status)
                    <option @selected(request('status') === $status)>{{ $status }}</option>@endforeach
                </select></div>
        </div><button class="btn btn-secondary" style="margin-top:14px">Terapkan filter</button>
    </form>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Program / pekerjaan</th>
                    <th>OPD</th>
                    <th>Tahun</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>@forelse($proposals as $proposal)
                <tr>
                    <td><a
                            href="{{ route('proposals.show', $proposal) }}"><strong>{{ $proposal->program->name }}</strong></a><br><span
                            class="muted">{{ Str::limit($proposal->work_description, 70) }}</span></td>
                    <td>{{ $proposal->opd->name }}</td>
                    <td>{{ $proposal->budget_year }}</td>
                    <td><strong>{{ $proposal->progress_percentage }}%</strong>
                        <div class="progress" style="margin-top:7px"><span
                                style="width:{{ $proposal->progress_percentage }}%"></span></div>
                    </td>
                    <td><span class="badge">{{ $proposal->status }}</span></td>
                    <td><a class="btn btn-secondary" href="{{ route('proposals.show', $proposal) }}">Detail</a></td>
            </tr>@empty<tr>
                    <td colspan="6" class="muted">Data tidak ditemukan.</td>
                </tr>@endforelse
            </tbody>
        </table>
        <div style="margin-top:18px">{{ $proposals->links() }}</div>
    </div>
@endsection