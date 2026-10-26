@extends('layouts.app')
@section('content')
    <div class="toolbar"><div><div class="eyebrow">Master data</div><h2 style="margin-bottom:0">Program</h2></div><a class="btn btn-primary" href="{{ route('programs.create') }}">+ Tambah program</a></div>
    <form id="program-directory-filter" class="card" method="get" style="margin-bottom:18px"><div class="field" style="margin:0"><label for="program-directory-search">Cari program</label><div class="searchable-select" id="program-directory-searchable">
        <input id="program-directory-search" name="search" type="search" value="{{ request('search') }}" placeholder="Ketik nama program..." autocomplete="off" role="combobox" aria-controls="program-directory-options" aria-expanded="false">
        <div id="program-directory-options" class="searchable-options" role="listbox">
            @foreach($programs as $program)
                <button type="button" class="searchable-option" role="option" data-program-name="{{ $program->name }}">{{ $program->name }}</button>
            @endforeach
        </div>
    </div></div></form>
    <div class="card"><table><thead><tr><th>Program</th><th>Jenis</th><th>OPD terkait</th><th>Status</th><th>Usulan</th><th></th></tr></thead><tbody>
        @forelse($programs as $program)<tr><td><strong>{{ $program->name }}</strong></td><td>{{ $program->type }}</td><td>{{ $program->opds->pluck('name')->join(', ') ?: '-' }}</td><td><span class="badge {{ $program->is_active ? '' : 'red' }}">{{ $program->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td>{{ $program->proposals_count }}</td><td><div class="actions"><a class="btn btn-secondary" href="{{ route('programs.edit', $program) }}">Edit</a><form method="post" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Hapus program ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td></tr>
        @empty<tr><td colspan="6" class="muted">Belum ada data program.</td></tr>@endforelse
    </tbody></table><div style="margin-top:18px">{{ $programs->links() }}</div></div>
<script>
    const programDirectoryForm = document.getElementById('program-directory-filter');
    const programDirectorySearchable = document.getElementById('program-directory-searchable');
    const programDirectorySearch = document.getElementById('program-directory-search');
    const programDirectoryOptions = Array.from(document.querySelectorAll('#program-directory-options .searchable-option'));

    function filterProgramDirectoryOptions() {
        const searchTerm = programDirectorySearch.value.trim().toLowerCase();

        programDirectoryOptions.forEach((option) => {
            option.hidden = searchTerm && !option.dataset.programName.toLowerCase().includes(searchTerm);
        });
    }

    programDirectorySearch.addEventListener('focus', () => {
        programDirectorySearchable.classList.add('is-open');
        programDirectorySearch.setAttribute('aria-expanded', 'true');
        filterProgramDirectoryOptions();
    });
    programDirectorySearch.addEventListener('input', filterProgramDirectoryOptions);
    programDirectoryOptions.forEach((option) => {
        option.addEventListener('click', () => {
            programDirectorySearch.value = option.dataset.programName;
            programDirectoryForm.submit();
        });
    });
    document.addEventListener('click', (event) => {
        if (!programDirectorySearchable.contains(event.target)) {
            programDirectorySearchable.classList.remove('is-open');
            programDirectorySearch.setAttribute('aria-expanded', 'false');
        }
    });
</script>
@endsection