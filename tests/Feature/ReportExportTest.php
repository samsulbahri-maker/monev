<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Program;
use App\Models\ProgressUpdate;
use App\Models\Proposal;
use App\Models\ProposalSupportingDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_contains_related_data_and_respects_year_and_opd_filters(): void
    {
        $user = User::factory()->create(['name' => 'Admin Penguji', 'email' => 'admin@example.test']);
        $includedOpd = Opd::create(['name' => 'OPD Termasuk']);
        $excludedOpd = Opd::create(['name' => 'OPD Tidak Termasuk']);
        $includedProgram = Program::create([
            'name' => 'Program Termasuk',
            'type' => 'Strategis',
            'is_active' => true,
        ]);
        $excludedProgram = Program::create([
            'name' => 'Program Tidak Termasuk',
            'type' => 'Lainnya',
            'is_active' => true,
        ]);
        $includedProposal = Proposal::create([
            'program_id' => $includedProgram->id,
            'opd_id' => $includedOpd->id,
            'created_by' => $user->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'work_description' => 'Pekerjaan termasuk',
            'location' => 'Lokasi termasuk',
            'main_budget' => 1000000,
            'supporting_budget' => 250000,
            'execution_date' => '2026-03-10',
            'status' => 'Proses Pekerjaan',
            'progress_percentage' => 40,
            'achievement' => 'Capaian termasuk',
        ]);
        Proposal::create([
            'program_id' => $excludedProgram->id,
            'opd_id' => $excludedOpd->id,
            'created_by' => $user->id,
            'budget_year' => 2026,
            'work_type' => 'Non Fisik Non Konstruksi',
            'status' => 'Tercantum Dalam DPA',
        ]);
        ProposalSupportingDocument::create([
            'proposal_id' => $includedProposal->id,
            'document_name' => 'DED',
            'amount' => 250000,
        ]);
        ProgressUpdate::create([
            'proposal_id' => $includedProposal->id,
            'month' => 3,
            'status' => 'Proses Pekerjaan',
            'percentage' => 40,
            'notes' => 'Catatan Maret',
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'year' => 2026,
            'opd_id' => $includedOpd->id,
        ]));

        $response->assertDownload('laporan-progres-2026.csv');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Nama Program', $content);
        $this->assertStringContainsString('Program Termasuk', $content);
        $this->assertStringContainsString('OPD Termasuk', $content);
        $this->assertStringContainsString('DED (Rp 250000.00)', $content);
        $this->assertStringContainsString('Catatan Maret', $content);
        $this->assertStringNotContainsString('Program Tidak Termasuk', $content);
    }
}
