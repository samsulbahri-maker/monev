<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $opds = collect([
            'Bappelitbangda',
            'Dinas Cipta Karya',
            'Dinas Sumber Daya Air',
            'Dinas Pariwisata',
            'Dinas Perindustrian dan Perdagangan',
        ])->mapWithKeys(fn (string $name) => [$name => Opd::updateOrCreate(['name' => $name])]);

        $priority = Program::updateOrCreate(
            ['name' => 'Pembangunan 10 Titik Tandon dan Long storage'],
            ['type' => 'Prioritas KDH/WKDH']
        );
        $priority->opds()->sync([$opds['Dinas Cipta Karya']->id, $opds['Dinas Sumber Daya Air']->id]);

        $strategic = Program::updateOrCreate(['name' => 'Dekranasda'], ['type' => 'Strategis']);
        $strategic->opds()->sync([$opds['Dinas Pariwisata']->id, $opds['Dinas Perindustrian dan Perdagangan']->id]);

        User::updateOrCreate(
            ['email' => 'admin@monev.test'],
            ['name' => 'Administrator Monev', 'password' => Hash::make('password'), 'opd_id' => $opds['Bappelitbangda']->id, 'role' => 'admin']
        );

        $proposal = Proposal::updateOrCreate(
            ['program_id' => $priority->id, 'opd_id' => $opds['Dinas Cipta Karya']->id, 'budget_year' => 2026],
            [
                'work_type' => 'Fisik Konstruksi',
                'work_description' => 'Pembangunan pada 10 Titik Tandon dan Long Storage',
                'location' => 'alamat',
                'status' => 'Tercantum Dalam DPA',
                'progress_percentage' => 0,
                'supporting_documents' => ['FS', 'DED', 'Pengawasan', 'KAK'],
            ]
        );

        foreach ([1 => 'Tercantum Dalam DPA', 2 => 'Tercantum Dalam DPA', 3 => 'Proses RUP'] as $month => $status) {
            $proposal->progressUpdates()->updateOrCreate(
                ['month' => $month],
                ['status' => $status, 'percentage' => 0]
            );
        }

        $proposal->supportingDocumentItems()->delete();
        $proposal->supportingDocumentItems()->createMany([
            ['document_name' => 'FS', 'amount' => 20000000],
            ['document_name' => 'DED', 'amount' => 15000000],
            ['document_name' => 'Pengawasan', 'amount' => 10000000],
            ['document_name' => 'KAK', 'amount' => 0],
        ]);
    }
}
