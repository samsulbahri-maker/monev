@php
$selectedProgramId = old('program_id', $proposal?->program_id);
$selectedOpdId = old('opd_id', $proposal?->opd_id);
$programOpds = $programs->mapWithKeys(fn ($program) => [
$program->id => $program->opds->map(fn ($opd) => ['id' => $opd->id, 'name' => $opd->name])->values()->all(),
])->all();
@endphp
@csrf
<div class="grid grid-2">
	<div class="field"><label for="program_search">Program prioritas / strategis *</label>
		<div class="searchable-select" id="proposal-program-searchable">
			<input id="program_search" type="search" placeholder="Cari nama program..." autocomplete="off" role="combobox" aria-controls="proposal-program-options" aria-expanded="false">
			<input id="program_id" type="hidden" name="program_id" value="{{ $selectedProgramId }}" required>
			<div id="proposal-program-options" class="searchable-options" role="listbox"></div>
		</div>
		<select id="program-source" aria-hidden="true" tabindex="-1">
			<option value="">Pilih program</option>@foreach($programs as $program)<option value="{{ $program->id }}" data-search="{{ strtolower($program->name.' '.$program->type) }}" @selected($selectedProgramId == $program->id)>{{ $program->name }} ({{ $program->type }})</option>@endforeach
		</select>
	</div>
		<div class="field"><label>OPD pelaksana *</label><select id="opd_id" name="opd_id" data-selected="{{ $selectedOpdId }}" required>
			<option value="">Pilih program terlebih dahulu</option>
		</select></div>
	<div class="field"><label>Tahun anggaran *</label><input type="number" name="budget_year" min="2000" max="2200" value="{{ old('budget_year', $proposal?->budget_year ?? date('Y')) }}" required></div>
	<div class="field"><label>Jenis pekerjaan *</label><select name="work_type" required>@foreach($workTypes as $type)<option @selected(old('work_type', $proposal?->work_type) === $type)>{{ $type }}</option>@endforeach</select></div>
</div>
<div class="field"><label>Deskripsi pekerjaan</label><textarea name="work_description">{{ old('work_description', $proposal?->work_description) }}</textarea></div>
<div class="grid grid-2">
	<div class="field"><label>Lokasi</label><textarea name="location" style="min-height:70px">{{ old('location', $proposal?->location) }}</textarea></div>
	<div class="field"><label>Link titik maps</label><input type="url" name="map_url" value="{{ old('map_url', $proposal?->map_url) }}" placeholder="https://maps.google.com/"></div>
	<div class="field"><label>Anggaran utama</label><input class="accounting-input" type="text" name="main_budget" inputmode="decimal" value="{{ old('main_budget', $proposal?->main_budget) }}" placeholder="Contoh: 1.000.000"></div>
	<div class="field">
		<div class="field"><label>Tanggal pelaksanaan</label><input type="date" name="execution_date" value="{{ old('execution_date', $proposal?->execution_date?->format('Y-m-d')) }}"></div>

	</div>
	<div class="field"><label>Dokumen pendukung dan anggaran</label>
		<div id="supporting-documents">@foreach($supportingDocuments as $index => $document)<div class="grid grid-2 supporting-document-row" style="grid-template-columns:1fr 1fr auto;align-items:end;margin-bottom:10px">
				<div><label>Nama dokumen</label><input name="supporting_documents[{{ $index }}][name]" value="{{ $document['name'] }}" placeholder="Contoh: FS"></div>
				<div><label>Anggaran pendukung</label><input class="accounting-input" type="text" name="supporting_documents[{{ $index }}][amount]" value="{{ $document['amount'] }}" inputmode="decimal" placeholder="Contoh: 20.000.000"></div><button type="button" class="btn btn-danger remove-supporting-document">Hapus</button>
			</div>@endforeach</div>
	</div>
	<div class="field">
		<button type="button" class="btn btn-secondary" id="add-supporting-document">+ Tambah dokumen</button>
		<div class="field"><label>Evidence</label><input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png">
			<div class="help">PDF atau gambar, maksimal 5 MB.</div>
		</div>

	</div><button class="btn btn-primary">Simpan usulan</button> <a class="btn btn-secondary" href="{{ route('proposals.index') }}">Batal</a>
	<script>
		const programSearch = document.getElementById('program_search');
		const programSelect = document.getElementById('program_id');
		const programSource = document.getElementById('program-source');
		const programSearchable = document.getElementById('proposal-program-searchable');
		const programOptionsElement = document.getElementById('proposal-program-options');
		const opdSelect = document.getElementById('opd_id');
		const programOpds = @json($programOpds);
		const programOptions = Array.from(programSource.options).map((option) => ({
			value: option.value,
			text: option.text,
			search: option.dataset.search ?? '',
		}));

		function renderProgramOpds() {
			const opds = programOpds[programSelect.value] ?? [];
			const selectedOpd = opdSelect.dataset.selected;
			const options = [new Option(
				programSelect.value
					? (opds.length ? 'Pilih OPD' : 'Program belum memiliki OPD')
					: 'Pilih program terlebih dahulu',
				'',
			)];

			opds.forEach((opd) => {
				options.push(new Option(opd.name, opd.id, false, String(opd.id) === String(selectedOpd)));
			});

			opdSelect.replaceChildren(...options);
			opdSelect.disabled = !programSelect.value || opds.length === 0;
		}

		function filterPrograms() {
			const searchTerm = programSearch.value.trim().toLowerCase();
			const matchingOptions = programOptions.filter((option) => !searchTerm || option.search.includes(searchTerm));

			programOptionsElement.replaceChildren(...matchingOptions.map((option) => {
				const element = document.createElement('button');
				element.type = 'button';
				element.className = 'searchable-option';
				element.setAttribute('role', 'option');
				element.textContent = option.value ? option.text : 'Pilih program';
				element.addEventListener('click', () => {
					programSelect.value = option.value;
					programSearch.value = option.value ? option.text : '';
					closeProgramOptions();
					renderProgramOpds();
				});
				return element;
			}));
		}

		programSearch.addEventListener('input', filterPrograms);
		programSearch.addEventListener('focus', () => {
			programSearchable.classList.add('is-open');
			programSearch.setAttribute('aria-expanded', 'true');
			filterPrograms();
		});
		document.addEventListener('click', (event) => {
			if (!programSearchable.contains(event.target)) {
				closeProgramOptions();
			}
		});
		programSelect.addEventListener('change', () => {
			opdSelect.dataset.selected = '';
			renderProgramOpds();
		});
		function closeProgramOptions() {
			programSearchable.classList.remove('is-open');
			programSearch.setAttribute('aria-expanded', 'false');
		}
		function renderSelectedProgram() {
			const selectedOption = programOptions.find((option) => option.value === String(programSelect.value));
			programSearch.value = selectedOption?.text ?? '';
		}
		programSelect.value = '{{ $selectedProgramId }}';
		renderSelectedProgram();
		renderProgramOpds();

		const documentContainer = document.getElementById('supporting-documents');
		let documentIndex = {{ count($supportingDocuments) }};
		document.getElementById('add-supporting-document').addEventListener('click', () => {
			documentContainer.insertAdjacentHTML('beforeend', `<div class="grid grid-2 supporting-document-row" style="grid-template-columns:1fr 1fr auto;align-items:end;margin-bottom:10px"><div><label>Nama dokumen</label><input name="supporting_documents[${documentIndex}][name]" placeholder="Contoh: FS"></div><div><label>Anggaran pendukung</label><input class="accounting-input" type="text" name="supporting_documents[${documentIndex}][amount]" inputmode="decimal" placeholder="Contoh: 20.000.000"></div><button type="button" class="btn btn-danger remove-supporting-document">Hapus</button></div>`);
			documentIndex++;
		});
		documentContainer.addEventListener('click', (event) => event.target.closest('.remove-supporting-document')?.closest('.supporting-document-row')?.remove());

		const proposalForm = documentContainer.closest('form');
		const accountingFormatter = new Intl.NumberFormat('id-ID', {
			maximumFractionDigits: 2,
		});

		function formatAccountingValue(input) {
			const value = input.value.replace(/[^\d,.-]/g, '');
			if (!value) {
				input.value = '';
				return;
			}

			const hasCommaDecimal = value.includes(',');
			const parts = hasCommaDecimal ? value.split(',') : value.split('.');
			const hasDecimal = parts.length > 1 && parts.at(-1).length <= 2;
			const integerPart = (hasCommaDecimal || !hasDecimal
				? parts.slice(0, hasCommaDecimal ? -1 : parts.length).join('')
				: parts.slice(0, -1).join('')).replace(/\D/g, '') || '0';
			const decimalPart = hasCommaDecimal
				? parts.at(-1).replace(/\D/g, '').slice(0, 2)
				: (hasDecimal ? parts.at(-1).replace(/\D/g, '').slice(0, 2) : '');

			input.value = accountingFormatter.format(Number(integerPart));
			if (value.endsWith(',') || decimalPart) {
				input.value += ',' + decimalPart;
			}
		}

		function normalizeAccountingValue(input) {
			input.value = input.value.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
		}

		documentContainer.closest('form').addEventListener('input', (event) => {
			if (event.target.classList.contains('accounting-input')) {
				formatAccountingValue(event.target);
			}
		});
		document.querySelectorAll('.accounting-input').forEach(formatAccountingValue);
		proposalForm.addEventListener('submit', () => {
			proposalForm.querySelectorAll('.accounting-input').forEach(normalizeAccountingValue);
		});
	</script>