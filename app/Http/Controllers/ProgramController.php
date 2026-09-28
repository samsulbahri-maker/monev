<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramController extends Controller
{
    private const TYPES = ['Prioritas KDH/WKDH', 'Strategis'];

    public function index(Request $request): View
    {
        $programs = Program::with('opds')
            ->withCount('proposals')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('programs.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $program = Program::create($this->validated($request));
        $program->opds()->sync($request->input('opd_ids', []));

        return redirect()->route('programs.index')->with('success', 'Program berhasil ditambahkan.');
    }

    public function edit(Program $program): View
    {
        return view('programs.edit', $this->formData($program));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $program->update($this->validated($request, $program));
        $program->opds()->sync($request->input('opd_ids', []));

        return redirect()->route('programs.index')->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        if ($program->proposals()->exists()) {
            return back()->withErrors(['program' => 'Program tidak dapat dihapus karena masih memiliki usulan.']);
        }

        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program berhasil dihapus.');
    }

    private function formData(?Program $program = null): array
    {
        return [
            'program' => $program,
            'opds' => Opd::orderBy('name')->get(),
            'types' => self::TYPES,
        ];
    }

    private function validated(Request $request, ?Program $program = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('programs', 'name')->ignore($program)],
            'type' => ['required', Rule::in(self::TYPES)],
            'is_active' => ['nullable', 'boolean'],
            'opd_ids' => ['nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:opds,id'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
