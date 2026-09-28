<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $year = $request->integer('year', now()->year);
        $query = Proposal::forUser($user)->with(['program', 'opd', 'progressUpdates'])->where('budget_year', $year);
        $query->when($request->filled('opd_id'), fn ($builder) => $builder->where('opd_id', $request->integer('opd_id')));

        return view('reports.index', [
            'year' => $year,
            'proposals' => $query->orderBy('opd_id')->orderBy('program_id')->get(),
            'opds' => $user->isPicOpd()
                ? Opd::whereIn('id', $user->assignedOpdIds())->orderBy('name')->get()
                : Opd::orderBy('name')->get(),
        ]);
    }
}
