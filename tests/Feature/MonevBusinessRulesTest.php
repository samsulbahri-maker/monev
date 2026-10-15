<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonevBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_opd_cannot_submit_the_same_program_twice_in_one_budget_year(): void
    {
        $user = User::factory()->create();
        $opd = Opd::create(['name' => 'OPD Pengujian']);
        $program = Program::create(['name' => 'Program Pengujian', 'type' => 'Prioritas KDH/WKDH']);
        $program->opds()->attach($opd);

        Proposal::create([
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'created_by' => $user->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
        ]);

        $response = $this->actingAs($user)->post(route('proposals.store'), [
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
            'progress_percentage' => 0,
        ]);

        $response->assertSessionHasErrors('program_id');
        $this->assertSame(1, Proposal::count());
    }

    public function test_progress_is_unique_per_month_and_updates_the_latest_proposal_summary(): void
    {
        $user = User::factory()->create();
        $opd = Opd::create(['name' => 'OPD Progres']);
        $program = Program::create(['name' => 'Program Progres', 'type' => 'Strategis']);
        $proposal = Proposal::create([
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'created_by' => $user->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
        ]);

        $this->actingAs($user)->post(route('proposals.progress.store', $proposal), [
            'month' => 1,
            'status' => 'Proses RUP',
            'percentage' => 20,
            'notes' => 'Input Januari',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('proposals.progress.store', $proposal), [
            'month' => 1,
            'status' => 'Proses Pengadaan',
            'percentage' => 35,
            'notes' => 'Perbarui Januari',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('proposals.progress.store', $proposal), [
            'month' => 2,
            'status' => 'Proses Pekerjaan',
            'percentage' => 50,
            'notes' => 'Input Februari',
        ])->assertRedirect();

        $this->assertSame(2, $proposal->progressUpdates()->count());
        $this->assertSame('Perbarui Januari', $proposal->progressUpdates()->where('month', 1)->value('notes'));
        $this->assertSame(50, $proposal->fresh()->progress_percentage);
        $this->assertSame('Proses Pekerjaan', $proposal->fresh()->status);
    }

    public function test_progress_can_be_edited_and_deleted_from_the_proposal_detail(): void
    {
        $user = User::factory()->create();
        $opd = Opd::create(['name' => 'OPD Edit Progres']);
        $program = Program::create(['name' => 'Program Edit Progres', 'type' => 'Strategis']);
        $proposal = Proposal::create([
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'created_by' => $user->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
        ]);
        $progressUpdate = $proposal->progressUpdates()->create([
            'month' => 1,
            'status' => 'Proses RUP',
            'percentage' => 20,
            'notes' => 'Catatan awal',
        ]);

        $this->actingAs($user)->put(route('proposals.progress.update', [$proposal, $progressUpdate]), [
            'month' => 1,
            'status' => 'Selesai',
            'percentage' => 100,
            'notes' => 'Catatan diperbarui',
        ])->assertRedirect(route('proposals.show', $proposal));

        $this->assertDatabaseHas('progress_updates', [
            'id' => $progressUpdate->id,
            'status' => 'Selesai',
            'percentage' => 100,
            'notes' => 'Catatan diperbarui',
        ]);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'Selesai',
            'progress_percentage' => 100,
        ]);

        $this->actingAs($user)->delete(route('proposals.progress.destroy', [$proposal, $progressUpdate]))
            ->assertRedirect();

        $this->assertDatabaseMissing('progress_updates', ['id' => $progressUpdate->id]);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => Proposal::STATUSES[0],
            'progress_percentage' => 0,
        ]);
    }

    public function test_supporting_documents_and_their_budgets_are_saved_as_rows(): void
    {
        $user = User::factory()->create();
        $opd = Opd::create(['name' => 'OPD Dokumen']);
        $program = Program::create(['name' => 'Program Dokumen', 'type' => 'Strategis']);
        $program->opds()->attach($opd);

        $response = $this->actingAs($user)->post(route('proposals.store'), [
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
            'progress_percentage' => 0,
            'supporting_documents' => [
                ['name' => 'FS', 'amount' => 20000000],
                ['name' => 'DED', 'amount' => 15000000],
            ],
        ]);

        $response->assertRedirect();
        $proposal = Proposal::latest('id')->first();
        $this->assertDatabaseHas('proposal_supporting_documents', [
            'proposal_id' => $proposal->id,
            'document_name' => 'FS',
            'amount' => 20000000,
        ]);
        $this->assertSame(35000000.0, (float) $proposal->fresh()->supporting_budget);
    }

    public function test_opd_options_follow_the_selected_program_and_mismatched_opd_is_rejected(): void
    {
        $user = User::factory()->create();
        $assignedOpd = Opd::create(['name' => 'OPD Program A']);
        $otherOpd = Opd::create(['name' => 'OPD Program B']);
        $program = Program::create(['name' => 'Program A', 'type' => 'Strategis']);
        $program->opds()->attach($assignedOpd);

        $this->actingAs($user)->get(route('proposals.create'))
            ->assertViewHas('programs', fn ($programs) => $programs->firstWhere('id', $program->id)->opds->pluck('id')->all() === [$assignedOpd->id]);

        $this->actingAs($user)->post(route('proposals.store'), [
            'program_id' => $program->id,
            'opd_id' => $otherOpd->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
            'progress_percentage' => 0,
        ])->assertSessionHasErrors('opd_id');
    }
}
