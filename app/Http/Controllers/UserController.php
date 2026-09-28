<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = ['admin', 'viewer_opd', 'viewer'];

    public function index(Request $request): View
    {
        $users = User::with('opd')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $opdIds = $data['opd_ids'] ?? [];
        unset($data['opd_ids']);
        $data['opd_id'] = $opdIds[0] ?? $data['opd_id'] ?? null;
        $user = User::create($data);
        $user->opds()->sync($opdIds);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $opdIds = $data['opd_ids'] ?? [];
        unset($data['opd_ids']);
        $data['opd_id'] = $opdIds[0] ?? $data['opd_id'] ?? null;
        $user->update($data);
        $user->opds()->sync($opdIds);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'User yang sedang login tidak dapat dihapus.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    private function formData(?User $user = null): array
    {
        return [
            'user' => $user,
            'opds' => Opd::orderBy('name')->get(),
            'roles' => [...self::ROLES, 'pic_opd'],
            'selectedOpds' => $user?->assignedOpdIds()->all() ?? [],
        ];
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'opd_id' => ['nullable', 'exists:opds,id'],
            'role' => ['required', Rule::in([...self::ROLES, 'pic_opd'])],
            'opd_ids' => [Rule::requiredIf($request->input('role') === 'pic_opd'), 'nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:opds,id'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
