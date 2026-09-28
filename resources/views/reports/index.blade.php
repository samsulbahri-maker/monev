@extends('layouts.app')
@section('content')
<div class="toolbar">
    <div>
        <div class="eyebrow">Rekap pelaksanaan</div>
        <h2 style="margin-bottom:0">Laporan progres bulanan</h2>
    </div>
</div>
<form class="card" method="get" style="margin-bottom:18px">
    <div class="actions">
        <div><label>Tahun anggaran</label><input type="number" name="year" value="{{ $year }}"></div>
        <div><label>OPD</label><select name="opd_id">
                <option value="">Semua OPD</option>@foreach($opds as $opd)
                    <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->name }}</option>
                @endforeach
            </select></div>
        <div style="padding-top:19px"><button class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Program / OPD</th>
                @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $month)
                <th>{{ $month }}</th>@endforeach
            </tr>
        </thead>
        <tbody>@forelse($proposals as $proposal)
            <tr>
                <td><strong>{{ $proposal->program->name }}</strong><br><span
                        class="muted">{{ $proposal->opd->name }}</span></td>
                @foreach(range(1, 12) as $month)@php($update = $proposal->progressUpdates->firstWhere('month', $month))
                <td>@if($update)<span
                class="badge">{{ $update->percentage }}%</span><br><small>{{ Str::limit($update->status, 15) }}</small>@else<span
                        class="muted">-</span>@endif</td>@endforeach
            </tr>@empty<tr>
                <td colspan="13" class="muted">Belum ada data laporan.</td>
            </tr>@endforelse
        </tbody>
    </table>
</div>
@endsection