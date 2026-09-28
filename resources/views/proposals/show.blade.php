@extends('layouts.app')
@section('content')
    <div class="detail-header">
        <div>
            <div class="eyebrow">{{ $proposal->program->type }} · {{ $proposal->budget_year }}</div>
            <div class="detail-title">{{ $proposal->program->name }}</div>
            <div class="muted">{{ $proposal->opd->name }} · {{ $proposal->work_type }}</div>
        </div>
        <div class="actions"><a class="btn btn-secondary" href="{{ route('proposals.edit', $proposal) }}">Edit</a>
            <form method="post" action="{{ route('proposals.destroy', $proposal) }}"
                onsubmit="return confirm('Hapus usulan ini?')">@csrf @method('delete')<button
                    class="btn btn-danger">Hapus</button></form>
        </div>
    </div>
    <div class="grid grid-2">
        <section class="card">
            <h3>Informasi kegiatan</h3>
            <dl>
                <dt class="muted">Deskripsi</dt>
                <dd>{{ $proposal->work_description ?: '-' }}</dd>
                <dt class="muted">Lokasi</dt>
                <dd>{{ $proposal->location ?: '-' }} @if($proposal->map_url)<a href="{{ $proposal->map_url }}"
                target="_blank" style="color:var(--teal)">Buka maps</a>@endif</dd>
                <dt class="muted">Anggaran utama</dt>
                <dd>Rp {{ number_format((float) $proposal->main_budget, 0, ',', '.') }}</dd>
                <dt class="muted">Dokumen pendukung</dt>
                <dd>@forelse($proposal->supportingDocumentItems as $document)
                    <div
                        style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:7px 0">
                        <span>{{ $document->document_name }}</span><strong>Rp
                            {{ number_format((float) $document->amount, 0, ',', '.') }}</strong>
                </div>@empty<span class="muted">Belum ada dokumen pendukung.</span>@endforelse
                </dd>
                <dt class="muted">Total anggaran pendukung</dt>
                <dd><strong>Rp
                        {{ number_format((float) $proposal->supportingDocumentItems->sum('amount'), 0, ',', '.') }}</strong>
                </dd>
                <dt class="muted">Capaian</dt>
                <dd>{{ $proposal->achievement ?: '-' }}</dd>
            </dl>
        </section>
        <section class="card">
            <h3>Progress terkini</h3>
            <div class="kpi">{{ $proposal->progress_percentage }}%</div>
            <div class="progress" style="margin:12px 0 16px;height:10px"><span
                    style="width:{{ $proposal->progress_percentage }}%"></span></div><span
                class="badge">{{ $proposal->status }}</span>
            <p class="muted" style="margin-top:20px">Tanggal pelaksanaan:
                {{ $proposal->execution_date?->format('d M Y') ?: 'Belum ditentukan' }}
            </p>
        </section>
    </div>
    <section class="card" style="margin-top:16px">
        <div class="toolbar">
            <h3>Progres bulanan</h3><span class="help">Satu catatan per bulan. Pilih bulan yang sama untuk
                memperbarui.</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Status</th>
                    <th>Persentase</th>
                    <th>Catatan</th>
                    <th>Evidence</th>
                </tr>
            </thead>
            <tbody>@forelse($proposal->progressUpdates as $update)
                <tr>
                    <td>{{ DateTime::createFromFormat('!m', $update->month)->format('F') }}</td>
                    <td><span class="badge">{{ $update->status }}</span></td>
                    <td>{{ $update->percentage }}%</td>
                    <td>{{ $update->notes ?: '-' }}</td>
                    <td>@if($update->evidence_path)<a href="{{ asset('storage/' . $update->evidence_path) }}"
                    target="_blank" style="color:var(--teal)">Lihat file</a>@else<span class="muted">-</span>@endif
                    </td>
            </tr>@empty<tr>
                    <td colspan="5" class="muted">Belum ada pembaruan progres.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </section>
    <section class="card" style="margin-top:16px">
        <h3>Tambah / ubah progres bulanan</h3>
        <form method="post" enctype="multipart/form-data" action="{{ route('proposals.progress.store', $proposal) }}"
            class="grid grid-2">@csrf<div class="field"><label>Bulan</label><select
                    name="month">@foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                    @endforeach
                </select></div>
            <div class="field"><label>Status</label><select
                    name="status">@foreach(['Tercantum Dalam DPA', 'Proses RUP', 'Proses Pengadaan', 'Proses Pekerjaan', 'Proses Pencairan', 'Selesai'] as $status)
                    <option>{{ $status }}</option>@endforeach
                </select></div>
            <div class="field"><label>Persentase</label><input type="number" name="percentage" min="0" max="100"
                    value="{{ $proposal->progress_percentage }}"></div>
            <div class="field"><label>Catatan</label><input name="notes" placeholder="Ringkasan progres bulan ini"></div>
            <div class="field"><label>Evidence</label><input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png">
                <div class="help">PDF atau gambar, maksimal 5 MB.</div>
            </div>
            <div><button class="btn btn-primary">Simpan progres</button></div>
        </form>
    </section>
@endsection