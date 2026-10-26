@extends('layouts.app')
@section('content')
<div class="toolbar">
    <div>
        <div class="eyebrow">Rekap pelaksanaan</div>
        <h2 style="margin-bottom:0">Laporan progres bulanan</h2>
    </div>
</div>
<form id="report-filter-form" class="card" method="get" style="margin-bottom:18px">
    <div class="actions report-filter-fields">
        <div><label>Tahun anggaran</label><input type="number" name="year" value="{{ $year }}"></div>
        <div><label>OPD</label><select name="opd_id">
                <option value="">Semua OPD</option>@foreach($opds as $opd)
                    <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->name }}</option>
                @endforeach
            </select></div>
        <div class="report-program-field"><label for="program-search">Program</label><div class="searchable-select" id="program-searchable">
            <input id="program-search" type="search" placeholder="Cari program..." autocomplete="off" role="combobox" aria-controls="program-options" aria-expanded="false">
            <input id="program_id" type="hidden" name="program_id" value="{{ request('program_id') }}">
            <div id="program-options" class="searchable-options" role="listbox"></div>
        </div>
        <select id="program-source" aria-hidden="true" tabindex="-1">
                <option value="">Semua program</option>@foreach($programs as $program)
                    <option value="{{ $program->id }}" data-opd-ids="{{ $program->opds->pluck('id')->join(',') }}" data-program-name="{{ $program->name }}">{{ $program->name }}</option>
                @endforeach
            </select></div>
        <div style="padding-top:19px" class="actions report-filter-submit">
            <button class="btn btn-primary" type="submit">Tampilkan</button>
            <button class="btn btn-secondary" type="submit" formaction="{{ route('reports.export') }}">Export Excel</button>
        </div>
    </div>
</form>
<script>
    const reportOpdSelect = document.querySelector('select[name="opd_id"]');
    const reportProgramSelect = document.getElementById('program_id');
    const reportProgramSource = document.getElementById('program-source');
    const reportProgramSearch = document.getElementById('program-search');
    const reportProgramOptionsElement = document.getElementById('program-options');
    const reportProgramSearchable = document.getElementById('program-searchable');
    const reportFilterForm = document.getElementById('report-filter-form');
    const reportProgramOptions = Array.from(reportProgramSource.options).map((option) => ({
        value: option.value,
        text: option.text,
        fullName: option.dataset.programName ?? option.text,
        opdIds: option.dataset.opdIds?.split(',').filter(Boolean) ?? [],
    }));

    function filterReportPrograms() {
        const selectedOpdId = reportOpdSelect.value;
        const selectedProgramId = reportProgramSelect.value;
        const availableOptions = reportProgramOptions.filter((option) => !option.value || !selectedOpdId || option.opdIds.includes(selectedOpdId));
        const selectedOption = availableOptions.find((option) => option.value === selectedProgramId);

        if (!selectedOption && selectedProgramId) {
            reportProgramSelect.value = '';
        }

        reportProgramSearch.value = selectedOption?.fullName ?? '';
        renderReportProgramOptions(availableOptions);
    }

    function renderReportProgramOptions(options) {
        const searchTerm = reportProgramSearch.value.trim().toLowerCase();
        const matchingOptions = options.filter((option) => !searchTerm || option.fullName.toLowerCase().includes(searchTerm));

        reportProgramOptionsElement.replaceChildren(...matchingOptions.map((option) => {
            const element = document.createElement('button');
            element.type = 'button';
            element.className = 'searchable-option';
            element.role = 'option';
            element.textContent = option.value ? option.fullName : 'Semua program';
            element.addEventListener('click', () => {
                reportProgramSelect.value = option.value;
                reportProgramSearch.value = option.fullName;
                closeReportProgramOptions();
                reportFilterForm.submit();
            });
            return element;
        }));
    }

    function openReportProgramOptions() {
        reportProgramSearchable.classList.add('is-open');
        reportProgramSearch.setAttribute('aria-expanded', 'true');
        renderReportProgramOptions(reportProgramOptions.filter((option) => !option.value || !reportOpdSelect.value || option.opdIds.includes(reportOpdSelect.value)));
    }

    function closeReportProgramOptions() {
        reportProgramSearchable.classList.remove('is-open');
        reportProgramSearch.setAttribute('aria-expanded', 'false');
    }

    reportOpdSelect.addEventListener('change', filterReportPrograms);
    reportOpdSelect.addEventListener('change', () => reportFilterForm.submit());
    reportProgramSearch.addEventListener('focus', openReportProgramOptions);
    reportProgramSearch.addEventListener('input', () => {
        reportProgramSelect.value = '';
        openReportProgramOptions();
    });
    document.addEventListener('click', (event) => {
        if (!reportProgramSearchable.contains(event.target)) {
            closeReportProgramOptions();
        }
    });
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