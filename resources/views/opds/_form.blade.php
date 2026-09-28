@csrf
<div class="field"><label>Nama OPD *</label><input name="name" value="{{ old('name', $opd?->name) }}" required autofocus></div>
<button class="btn btn-primary">Simpan OPD</button> <a class="btn btn-secondary" href="{{ route('opds.index') }}">Batal</a>