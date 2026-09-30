<?php

namespace App\Http\Controllers;

use App\Models\ProgressUpdate;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgressController extends Controller
{
    public function store(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($request->user()->canManageProposals(), 403);
        abort_unless($proposal->isAccessibleTo($request->user()), 403);

        $data = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'status' => ['required', Rule::in(Proposal::STATUSES)],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'local');
        }
        unset($data['evidence']);

        $proposal->progressUpdates()->updateOrCreate(['month' => $data['month']], $data);
        $latest = $proposal->progressUpdates()->latest('month')->first();
        $proposal->update([
            'status' => $latest->status,
            'progress_percentage' => $latest->percentage,
        ]);

        return back()->with('success', 'Progres bulanan berhasil disimpan.');
    }

    public function evidence(Request $request, Proposal $proposal, ProgressUpdate $progressUpdate): BinaryFileResponse
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);
        abort_unless($progressUpdate->evidence_path && Storage::disk('local')->exists($progressUpdate->evidence_path), 404);

        return response()->download(Storage::disk('local')->path($progressUpdate->evidence_path));
    }
}
