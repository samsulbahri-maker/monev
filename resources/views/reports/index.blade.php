@extends('layouts.app')
@section('content')
<div class="toolbar">
    <div>
        <div class="eyebrow">Rekap pelaksanaan</div>
        <h2 style="margin-bottom:0">Laporan progres bulanan</h2>
    </div>
</div>
<form id="report-filter-form" class="card" method="get" style="margin-bottom:18px">
    <div class="actions">
        <div><label>Tahun anggaran</label><input type="number" name="year" value="{{ $year }}"></div>
        <div><label>OPD</label><select name="opd_id">
                <option value="">Semua OPD</option>@foreach($opds as $opd)
                    <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->name }}</option>
                @endforeach
            </select></div>
        <div><label>Program</label><select id="program_id" name="program_id">
                <option value="">Semua program</option>@foreach($programs as $program)
                    <option value="{{ $program->id }}" data-opd-ids="{{ $program->opds->pluck('id')->join(',') }}" @selected(request('program_id') == $program->id)>{{ $program->name }} ({{ $program->type }})</option>
                @endforeach
            </select></div>
        <div style="padding-top:19px" class="actions">
            <button class="btn btn-primary" type="submit">Tampilkan</button>
            <button class="btn btn-secondary" type="submit" formaction="{{ route('reports.export') }}">Export Excel</button>
        </div>
    </div>
</form>
<script>
    const reportOpdSelect = document.querySelector('select[name="opd_id"]');
    const reportProgramSelect = document.getElementById('program_id');
    const reportFilterForm = document.getElementById('report-filter-form');
    const reportProgramOptions = Array.from(reportProgramSelect.options).map((option) => ({
        value: option.value,
        text: option.text,
        opdIds: option.dataset.opdIds?.split(',').filter(Boolean) ?? [],
    }));

    function filterReportPrograms() {
        const selectedOpdId = reportOpdSelect.value;
        const selectedProgramId = reportProgramSelect.value;
        const options = reportProgramOptions.filter((option) => !option.value || !selectedOpdId || option.opdIds.includes(selectedOpdId));

        reportProgramSelect.replaceChildren(...options.map((option) => {
            return new Option(option.text, option.value, false, option.value === selectedProgramId);
        }));

        if (!options.some((option) => option.value === selectedProgramId)) {
            reportProgramSelect.value = '';
        }
    }

    reportOpdSelect.addEventListener('change', filterReportPrograms);
    reportOpdSelect.addEventListener('change', () => reportFilterForm.submit());
    reportProgramSelect.addEventListener('change', () => reportFilterForm.submit());
    filterReportPrograms();
</script>
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
                        class="muted">{{ $proposal->opd->name }}</span><br>
                        <span
                        class="muted">{{ $proposal->location }}</span>
                    </td>
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