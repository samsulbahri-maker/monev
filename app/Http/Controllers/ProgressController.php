<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function store(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);

        $data = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'status' => ['required', 'string', 'max:40'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'public');
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
}
