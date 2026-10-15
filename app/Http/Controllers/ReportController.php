<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $year = $request->integer('year', now()->year);

        return view('reports.index', [
            'year' => $year,
            'proposals' => $this->proposalQuery($request)->orderBy('opd_id')->orderBy('program_id')->get(),
            'opds' => $user->isPicOpd()
                ? Opd::whereIn('id', $user->assignedOpdIds())->orderBy('name')->get()
                : Opd::orderBy('name')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $proposals = $this->proposalQuery($request)
            ->orderBy('opd_id')
            ->orderBy('program_id')
            ->get();

        return response()->streamDownload(function () use ($proposals): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $this->exportHeaders());

            foreach ($proposals as $proposal) {
                $progressByMonth = $proposal->progressUpdates->keyBy('month');
                $supportingDocuments = $proposal->supportingDocumentItems
                    ->map(fn ($document): string => $document->document_name.' (Rp '.$document->amount.')')
                    ->implode('; ');
                $row = [
                    $proposal->id,
                    $proposal->budget_year,
                    $proposal->program_id,
                    $proposal->program->name,
                    $proposal->program->type,
                    $proposal->program->is_active ? 'Aktif' : 'Tidak aktif',
                    $proposal->opd_id,
                    $proposal->opd->name,
                    $proposal->created_by,
                    $proposal->creator?->name,
                    $proposal->creator?->email,
                    $proposal->work_type,
                    $proposal->work_description,
                    $proposal->location,
                    $proposal->map_url,
                    $proposal->main_budget,
                    $proposal->supporting_budget,
                    $supportingDocuments,
                    $proposal->execution_date?->format('Y-m-d'),
                    $proposal->status,
                    $proposal->progress_percentage,
                    $proposal->achievement,
                    $proposal->evidence_path,
                ];

                foreach (range(1, 12) as $month) {
                    $update = $progressByMonth->get($month);
                    $row[] = $update?->status;
                    $row[] = $update?->percentage;
                    $row[] = $update?->notes;
                    $row[] = $update?->evidence_path;
                    $row[] = $update?->updated_at?->format('Y-m-d H:i:s');
                }

                fputcsv($output, $row);
            }

            fclose($output);
        }, 'laporan-progres-'.$request->integer('year', now()->year).'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function proposalQuery(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();

        return Proposal::forUser($user)
            ->with([
                'program',
                'opd',
                'creator',
                'supportingDocumentItems',
                'progressUpdates' => fn ($query) => $query->orderBy('month'),
            ])
            ->where('budget_year', $request->integer('year', now()->year))
            ->when($request->filled('opd_id'), fn ($query) => $query->where('opd_id', $request->integer('opd_id')));
    }

    /**
     * @return list<string>
     */
    private function exportHeaders(): array
    {
        $headers = [
            'ID Usulan',
            'Tahun Anggaran',
            'ID Program',
            'Nama Program',
            'Jenis Program',
            'Status Program',
            'ID OPD',
            'Nama OPD',
            'ID Pembuat',
            'Nama Pembuat',
            'Email Pembuat',
            'Jenis Pekerjaan',
            'Deskripsi Pekerjaan',
            'Lokasi',
            'URL Peta',
            'Anggaran Utama',
            'Total Anggaran Pendukung',
            'Dokumen Pendukung',
            'Tanggal Pelaksanaan',
            'Status Usulan',
            'Progres Terakhir (%)',
            'Capaian',
            'Bukti Usulan',
        ];

        foreach (['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $month) {
            $headers[] = $month.' - Status';
            $headers[] = $month.' - Progres (%)';
            $headers[] = $month.' - Catatan';
            $headers[] = $month.' - Bukti';
            $headers[] = $month.' - Diperbarui';
        }

        return $headers;
    }
}
