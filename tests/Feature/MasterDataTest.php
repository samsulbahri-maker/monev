<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_master_pages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get(route('opds.index'))->assertOk();
        $this->actingAs($user)->get(route('opds.create'))->assertOk();
        $this->actingAs($user)->get(route('programs.index'))->assertOk();
        $this->actingAs($user)->get(route('users.index'))->assertOk();
    }

    public function test_program_can_be_created_with_opd_assignments(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $opd = Opd::create(['name' => 'OPD Master']);

        $this->actingAs($user)->post(route('programs.store'), [
            'name' => 'Program Master',
            'type' => 'Strategis',
            'is_active' => 1,
            'opd_ids' => [$opd->id],
        ])->assertRedirect(route('programs.index'));

        $program = Program::where('name', 'Program Master')->firstOrFail();
        $this->assertTrue($program->opds->contains($opd));
    }

    public function test_user_can_be_created_with_a_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $opd = Opd::create(['name' => 'OPD User']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'User Master',
            'email' => 'master@example.test',
            'opd_id' => $opd->id,
            'role' => 'viewer_opd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $created = User::where('email', 'master@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $created->password));
    }
}
