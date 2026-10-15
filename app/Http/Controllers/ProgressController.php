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

    public function edit(Request $request, Proposal $proposal, ProgressUpdate $progressUpdate): RedirectResponse
    {
        abort_unless($request->user()->canManageProposals(), 403);
        abort_unless($proposal->isAccessibleTo($request->user()), 403);

        return redirect()->route('proposals.show', [
            'proposal' => $proposal,
            'edit_progress' => $progressUpdate->id,
        ]);
    }

    public function update(Request $request, Proposal $proposal, ProgressUpdate $progressUpdate): RedirectResponse
    {
        abort_unless($request->user()->canManageProposals(), 403);
        abort_unless($proposal->isAccessibleTo($request->user()), 403);

        $data = $this->validated($request);
        if ($request->hasFile('evidence')) {
            $this->deleteEvidence($progressUpdate);
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'local');
        }

        $progressUpdate->update($data);
        $this->syncProposalSummary($proposal);

        return redirect()->route('proposals.show', $proposal)->with('success', 'Progres bulanan berhasil diperbarui.');
    }

    public function destroy(Request $request, Proposal $proposal, ProgressUpdate $progressUpdate): RedirectResponse
    {
        abort_unless($request->user()->canManageProposals(), 403);
        abort_unless($proposal->isAccessibleTo($request->user()), 403);

        $this->deleteEvidence($progressUpdate);
        $progressUpdate->delete();
        $this->syncProposalSummary($proposal);

        return back()->with('success', 'Progres bulanan berhasil dihapus.');
    }

    public function evidence(Request $request, Proposal $proposal, ProgressUpdate $progressUpdate): BinaryFileResponse
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);
        abort_unless($progressUpdate->evidence_path && Storage::disk('local')->exists($progressUpdate->evidence_path), 404);

        return response()->download(Storage::disk('local')->path($progressUpdate->evidence_path));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'status' => ['required', Rule::in(Proposal::STATUSES)],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
    }

    private function syncProposalSummary(Proposal $proposal): void
    {
        $latest = $proposal->progressUpdates()->latest('month')->first();
        $proposal->update([
            'status' => $latest?->status ?? Proposal::STATUSES[0],
            'progress_percentage' => $latest?->percentage ?? 0,
        ]);
    }

    private function deleteEvidence(ProgressUpdate $progressUpdate): void
    {
        if ($progressUpdate->evidence_path) {
            Storage::disk('local')->delete($progressUpdate->evidence_path);
        }
    }
}
