<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = request()->user();
        $year = request('year', now()->year);
        $proposals = Proposal::forUser($user)->where('budget_year', $year);

        return view('dashboard', [
            'year' => $year,
            'totalProposals' => (clone $proposals)->count(),
            'totalBudget' => (clone $proposals)->sum('main_budget'),
            'completed' => (clone $proposals)->where('status', 'Selesai')->count(),
            'activeOpds' => $user->isPicOpd() ? $user->assignedOpdIds()->count() : Opd::count(),
            'programCount' => Program::where('is_active', true)
                ->when($user->isPicOpd(), fn ($query) => $query->whereHas('opds', fn ($opdQuery) => $opdQuery->whereIn('opds.id', $user->assignedOpdIds())))
                ->count(),
            'recentProposals' => Proposal::forUser($user)->with(['program', 'opd'])->latest()->limit(8)->get(),
            'statusCounts' => (clone $proposals)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
