<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_modify_a_proposal(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $proposal = $this->createProposal();

        $this->actingAs($viewer)
            ->delete(route('proposals.destroy', $proposal))
            ->assertForbidden();

        $this->assertDatabaseHas('proposals', ['id' => $proposal->id]);
    }

    public function test_invalid_status_is_rejected_for_progress_updates(): void
    {
        $user = User::factory()->create(['role' => 'viewer_opd']);
        $proposal = $this->createProposal();

        $this->actingAs($user)
            ->post(route('proposals.progress.store', $proposal), [
                'month' => 1,
                'status' => 'Injected status',
                'percentage' => 10,
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_evidence_is_served_from_private_storage(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'viewer']);
        $proposal = $this->createProposal(['evidence_path' => 'evidence/private.pdf']);
        Storage::disk('local')->put($proposal->evidence_path, 'private evidence');

        $this->actingAs($user)
            ->get(route('proposals.evidence', $proposal))
            ->assertDownload('private.pdf');
    }

    private function createProposal(array $attributes = []): Proposal
    {
        $opd = Opd::create(['name' => fake()->unique()->company()]);
        $program = Program::create(['name' => fake()->unique()->sentence(3), 'type' => 'Strategis']);
        $program->opds()->attach($opd);

        return Proposal::create(array_merge([
            'program_id' => $program->id,
            'opd_id' => $opd->id,
            'budget_year' => 2026,
            'work_type' => 'Fisik Konstruksi',
            'status' => 'Tercantum Dalam DPA',
        ], $attributes));
    }
}
