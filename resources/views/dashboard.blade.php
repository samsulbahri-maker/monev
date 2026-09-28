@extends('layouts.app')
@section('content')
    <div class="eyebrow">Tahun anggaran {{ $year }}</div>
    <h2>Ringkasan pelaksanaan</h2>
    <div class="grid grid-4" style="margin-bottom:22px">
        <div class="card stat">
            <div class="stat-label">Total usulan</div>
            <div class="stat-value">{{ $totalProposals }}</div>
            <div class="muted">kegiatan terdaftar</div>
        </div>
        <div class="card stat">
            <div class="stat-label">Total anggaran utama</div>
            <div class="stat-value" style="font-size:22px">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
            <div class="muted">pagu terdata</div>
        </div>
        <div class="card stat">
            <div class="stat-label">Program aktif</div>
            <div class="stat-value">{{ $programCount }}</div>
            <div class="muted">prioritas dan strategis</div>
        </div>
        <div class="card stat">
            <div class="stat-label">Usulan selesai</div>
            <div class="stat-value">{{ $completed }}</div>
            <div class="muted">dari {{ $totalProposals }} usulan</div>
        </div>
    </div>
    <div class="grid grid-2">
        <section class="card">
            <div class="toolbar">
                <h3>Usulan terbaru</h3><a class="btn btn-secondary" href="{{ route('proposals.index') }}">Lihat semua</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>OPD</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>@forelse($recentProposals as $proposal)
                    <tr>
                        <td><a
                                href="{{ route('proposals.show', $proposal) }}"><strong>{{ $proposal->program->name }}</strong></a><br><span
                                class="muted">{{ $proposal->budget_year }} · {{ $proposal->work_type }}</span></td>
                        <td>{{ $proposal->opd->name }}</td>
                        <td><span class="badge">{{ $proposal->status }}</span>
                            <div class="progress" style="margin-top:7px"><span
                                    style="width:{{ $proposal->progress_percentage }}%"></span></div>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3" class="muted">Belum ada usulan.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </section>
        <section class="card">
            <h3>Status pelaksanaan</h3>@forelse($statusCounts as $status => $total)
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line)">
                    <span>{{ $status }}</span><strong>{{ $total }}</strong>
            </div>@empty<p class="muted">Belum ada data untuk
            tahun ini.</p>@endforelse
        </section>
    </div>
@endsection