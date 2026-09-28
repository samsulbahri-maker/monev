<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicOpdAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pic_opd_can_access_dashboard_proposals_and_reports_for_multiple_assigned_opds(): void
    {
        $pic = User::factory()->create(['role' => 'pic_opd']);
        $assignedOpd = Opd::create(['name' => 'OPD PIC A']);
        $secondAssignedOpd = Opd::create(['name' => 'OPD PIC B']);
        $outsideOpd = Opd::create(['name' => 'OPD Lain']);
        $pic->opds()->sync([$assignedOpd->id, $secondAssignedOpd->id]);
        $program = Program::create(['name' => 'Program PIC', 'type' => 'Strategis']);
        $program->opds()->sync([$assignedOpd->id, $secondAssignedOpd->id]);
        Proposal::create(['program_id' => $program->id, 'opd_id' => $assignedOpd->id, 'budget_year' => 2026, 'work_type' => 'Fisik Konstruksi', 'status' => 'Tercantum Dalam DPA']);
        Proposal::create(['program_id' => $program->id, 'opd_id' => $outsideOpd->id, 'budget_year' => 2026, 'work_type' => 'Fisik Konstruksi', 'status' => 'Tercantum Dalam DPA']);

        $this->actingAs($pic)->get(route('dashboard'))->assertOk()->assertViewHas('totalProposals', 1);
        $this->actingAs($pic)->get(route('proposals.index'))->assertOk()->assertViewHas('proposals', fn ($proposals) => $proposals->total() === 1);
        $this->actingAs($pic)->get(route('reports.index'))->assertOk()->assertViewHas('proposals', fn ($proposals) => $proposals->count() === 1);
    }

    public function test_pic_opd_cannot_open_a_proposal_from_an_unassigned_opd_or_master_data(): void
    {
        $pic = User::factory()->create(['role' => 'pic_opd']);
        $assignedOpd = Opd::create(['name' => 'OPD PIC']);
        $outsideOpd = Opd::create(['name' => 'OPD Terbatas']);
        $pic->opds()->attach($assignedOpd);
        $program = Program::create(['name' => 'Program Terbatas', 'type' => 'Strategis']);
        $proposal = Proposal::create(['program_id' => $program->id, 'opd_id' => $outsideOpd->id, 'budget_year' => 2026, 'work_type' => 'Fisik Konstruksi', 'status' => 'Tercantum Dalam DPA']);

        $this->actingAs($pic)->get(route('proposals.show', $proposal))->assertForbidden();
        $this->actingAs($pic)->post(route('proposals.progress.store', $proposal), [
            'month' => 1,
            'status' => 'Proses RUP',
            'percentage' => 20,
        ])->assertForbidden();
        $this->actingAs($pic)->get(route('opds.index'))->assertForbidden();
    }

    public function test_pic_opd_can_be_created_with_multiple_opds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $opds = collect([
            Opd::create(['name' => 'OPD Multi A']),
            Opd::create(['name' => 'OPD Multi B']),
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'PIC Multi OPD',
            'email' => 'pic@example.test',
            'role' => 'pic_opd',
            'opd_ids' => $opds->pluck('id')->all(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $pic = User::where('email', 'pic@example.test')->firstOrFail();
        $this->assertEqualsCanonicalizing($opds->pluck('id')->all(), $pic->assignedOpdIds()->all());
    }
}
