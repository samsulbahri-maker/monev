<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function index(Request $request): View
    {
        $opds = Opd::withCount(['programs', 'proposals'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('opds.index', compact('opds'));
    }

    public function create(): View
    {
        return view('opds.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Opd::create($this->validated($request));

        return redirect()->route('opds.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    public function edit(Opd $opd): View
    {
        return view('opds.edit', compact('opd'));
    }

    public function update(Request $request, Opd $opd): RedirectResponse
    {
        $opd->update($this->validated($request, $opd));

        return redirect()->route('opds.index')->with('success', 'OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd): RedirectResponse
    {
        if ($opd->programs()->exists() || $opd->proposals()->exists()) {
            return back()->withErrors(['opd' => 'OPD tidak dapat dihapus karena masih digunakan.']);
        }

        $opd->delete();

        return redirect()->route('opds.index')->with('success', 'OPD berhasil dihapus.');
    }

    private function validated(Request $request, ?Opd $opd = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('opds', 'name')->ignore($opd)],
        ]);
    }
}
