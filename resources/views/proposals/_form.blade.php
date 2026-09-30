@php
	$selectedProgramId = old('program_id', $proposal?->program_id);
	$selectedOpdId = old('opd_id', $proposal?->opd_id);
	$programOpds = $programs->mapWithKeys(fn ($program) => [
		$program->id => $program->opds->map(fn ($opd) => ['id' => $opd->id, 'name' => $opd->name])->values()->all(),
	])->all();
@endphp
@csrf
<div class="grid grid-2"><div class="field"><label>Program prioritas / strategis *</label><select id="program_id" name="program_id" required><option value="">Pilih program</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected($selectedProgramId == $program->id)>{{ $program->name }} ({{ $program->type }})</option>@endforeach</select></div><div class="field"><label>OPD pelaksana *</label><select id="opd_id" name="opd_id" data-selected="{{ $selectedOpdId }}" required disabled><option value="">Pilih program terlebih dahulu</option></select></div><div class="field"><label>Tahun anggaran *</label><input type="number" name="budget_year" min="2000" max="2200" value="{{ old('budget_year', $proposal?->budget_year ?? date('Y')) }}" required></div><div class="field"><label>Jenis pekerjaan *</label><select name="work_type" required>@foreach($workTypes as $type)<option @selected(old('work_type', $proposal?->work_type) === $type)>{{ $type }}</option>@endforeach</select></div></div>
<div class="field"><label>Deskripsi pekerjaan</label><textarea name="work_description">{{ old('work_description', $proposal?->work_description) }}</textarea></div><div class="grid grid-2"><div class="field"><label>Lokasi</label><textarea name="location" style="min-height:70px">{{ old('location', $proposal?->location) }}</textarea></div><div class="field"><label>Link titik maps</label><input type="url" name="map_url" value="{{ old('map_url', $proposal?->map_url) }}" placeholder="https://maps.google.com/"></div><div class="field"><label>Anggaran utama</label><input type="number" name="main_budget" min="0" step="0.01" value="{{ old('main_budget', $proposal?->main_budget) }}"></div><div class="field"><div class="field"><label>Tanggal pelaksanaan</label><input type="date" name="execution_date" value="{{ old('execution_date', $proposal?->execution_date?->format('Y-m-d')) }}"></div><div class="field"><label>Status</label><select name="status">@foreach($statuses as $status)<option @selected(old('status', $proposal?->status ?? 'Tercantum Dalam DPA') === $status)>{{ $status }}</option>@endforeach</select></div></div>
<div class="field"><label>Dokumen pendukung dan anggaran</label><div id="supporting-documents">@foreach($supportingDocuments as $index => $document)<div class="grid grid-2 supporting-document-row" style="grid-template-columns:1fr 1fr auto;align-items:end;margin-bottom:10px"><div><label>Nama dokumen</label><input name="supporting_documents[{{ $index }}][name]" value="{{ $document['name'] }}" placeholder="Contoh: FS"></div><div><label>Anggaran pendukung</label><input type="number" name="supporting_documents[{{ $index }}][amount]" value="{{ $document['amount'] }}" min="0" step="0.01" placeholder="20000000"></div><button type="button" class="btn btn-danger remove-supporting-document">Hapus</button></div>@endforeach</div><button type="button" class="btn btn-secondary" id="add-supporting-document">+ Tambah dokumen</button></div><div class="grid grid-2"><div class="field"><label>Persentase progres</label><input type="number" name="progress_percentage" min="0" max="100" value="{{ old('progress_percentage', $proposal?->progress_percentage ?? 0) }}"></div><div class="field"><label>Capaian</label><textarea name="achievement" style="min-height:70px">{{ old('achievement', $proposal?->achievement) }}</textarea></div></div>
<div class="field"><label>Evidence</label><input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png"><div class="help">PDF atau gambar, maksimal 5 MB.</div></div><button class="btn btn-primary">Simpan usulan</button> <a class="btn btn-secondary" href="{{ route('proposals.index') }}">Batal</a>
<script>
	const programSelect = document.getElementById('program_id');
	const opdSelect = document.getElementById('opd_id');
	const programOpds = @json($programOpds);

	function renderProgramOpds() {
		const opds = programOpds[programSelect.value] ?? [];
		const selectedOpd = opdSelect.dataset.selected;
		opdSelect.replaceChildren(new Option(opds.length ? 'Pilih OPD' : 'Program belum memiliki OPD', ''));
		opds.forEach((opd) => opdSelect.add(new Option(opd.name, opd.id)));
		opdSelect.disabled = opds.length === 0;
		if (opds.some((opd) => String(opd.id) === String(selectedOpd))) {
			opdSelect.value = selectedOpd;
		}
	}

	programSelect.addEventListener('change', () => {
		opdSelect.dataset.selected = '';
		renderProgramOpds();
	});
	renderProgramOpds();

	const documentContainer = document.getElementById('supporting-documents');
	let documentIndex = {{ count($supportingDocuments) }};
	document.getElementById('add-supporting-document').addEventListener('click', () => {
		documentContainer.insertAdjacentHTML('beforeend', `<div class="grid grid-2 supporting-document-row" style="grid-template-columns:1fr 1fr auto;align-items:end;margin-bottom:10px"><div><label>Nama dokumen</label><input name="supporting_documents[${documentIndex}][name]" placeholder="Contoh: FS"></div><div><label>Anggaran pendukung</label><input type="number" name="supporting_documents[${documentIndex}][amount]" min="0" step="0.01" placeholder="20000000"></div><button type="button" class="btn btn-danger remove-supporting-document">Hapus</button></div>`);
		documentIndex++;
	});
	documentContainer.addEventListener('click', (event) => event.target.closest('.remove-supporting-document')?.closest('.supporting-document-row')?.remove());
</script>
