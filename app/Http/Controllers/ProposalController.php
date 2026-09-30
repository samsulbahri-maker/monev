<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Program;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Proposal::with(['program', 'opd'])->forUser($request->user());
        $query->when($request->filled('year'), fn ($builder) => $builder->where('budget_year', $request->integer('year')));
        $query->when($request->filled('opd_id'), fn ($builder) => $builder->where('opd_id', $request->integer('opd_id')));
        $query->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')));
        $query->when($request->filled('search'), function ($builder) use ($request) {
            $term = '%'.$request->string('search').'%';
            $builder->where(function ($nested) use ($term) {
                $nested->where('work_description', 'like', $term)->orWhere('location', 'like', $term)->orWhereHas('program', fn ($program) => $program->where('name', 'like', $term));
            });
        });

        return view('proposals.index', [
            'proposals' => $query->latest()->paginate(12)->withQueryString(),
            'opds' => $this->accessibleOpds($request->user()),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('proposals.create', $this->formData(null, $request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $supportingDocuments = $this->supportingDocuments($data['supporting_documents'] ?? []);
        unset($data['supporting_documents']);
        $data['supporting_budget'] = collect($supportingDocuments)->sum(fn (array $document) => $document['amount']);
        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'public');
        }
        $proposal = Proposal::create($data + ['created_by' => $request->user()->id]);
        $proposal->supportingDocumentItems()->createMany($supportingDocuments);

        return redirect()->route('proposals.show', $proposal)->with('success', 'Usulan kegiatan berhasil ditambahkan.');
    }

    public function show(Request $request, Proposal $proposal): View
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);
        $proposal->load(['program', 'opd', 'supportingDocumentItems', 'progressUpdates' => fn ($query) => $query->orderBy('month')]);

        return view('proposals.show', compact('proposal'));
    }

    public function edit(Request $request, Proposal $proposal): View
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);
        $proposal->load('supportingDocumentItems');

        return view('proposals.edit', $this->formData($proposal, $request->user()));
    }

    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->isAccessibleTo($request->user()), 403);
        $data = $this->validated($request);
        $supportingDocuments = $this->supportingDocuments($data['supporting_documents'] ?? []);
        unset($data['supporting_documents']);
        $data['supporting_budget'] = collect($supportingDocuments)->sum(fn (array $document) => $document['amount']);
        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence', 'public');
        }
        $proposal->update($data);
        $proposal->supportingDocumentItems()->delete();
        $proposal->supportingDocumentItems()->createMany($supportingDocuments);

        return redirect()->route('proposals.show', $proposal)->with('success', 'Usulan kegiatan diperbarui.');
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->isAccessibleTo(request()->user()), 403);
        $proposal->delete();

        return redirect()->route('proposals.index')->with('success', 'Usulan kegiatan dihapus.');
    }

    private function formData(?Proposal $proposal, User $user): array
    {
        $supportingDocuments = $proposal?->supportingDocumentItems
            ->map(fn ($document) => ['name' => $document->document_name, 'amount' => $document->amount])
            ->values()
            ->all() ?? [];

        return [
            'proposal' => $proposal,
            'supportingDocuments' => old('supporting_documents', $supportingDocuments ?: [['name' => '', 'amount' => '']]),
            'programs' => Program::with(['opds' => fn ($query) => $user->isPicOpd()
                ? $query->whereIn('opds.id', $user->assignedOpdIds())
                : $query])
                ->where('is_active', true)
                ->when($user->isPicOpd(), fn ($query) => $query->whereHas('opds', fn ($opdQuery) => $opdQuery->whereIn('opds.id', $user->assignedOpdIds())))
                ->orderBy('type')->orderBy('name')->get(),
            'opds' => $this->accessibleOpds($user),
            'statuses' => $this->statuses(),
            'workTypes' => ['Fisik Konstruksi', 'Fisik Non Konstruksi', 'Non Fisik Non Konstruksi'],
        ];
    }

    private function validated(Request $request): array
    {
        $proposal = $request->route('proposal');

        return $request->validate([
            'program_id' => [
                'required',
                'exists:programs,id',
                Rule::unique('proposals')->where(fn ($query) => $query
                    ->where('opd_id', $request->integer('opd_id'))
                    ->where('budget_year', $request->integer('budget_year'))
                )->ignore($proposal?->id),
            ],
            'opd_id' => [
                'required',
                'exists:opds,id',
                Rule::exists('program_opd', 'opd_id')->where(fn ($query) => $query->where('program_id', $request->integer('program_id'))),
                Rule::when($request->user()->isPicOpd(), Rule::in($request->user()->assignedOpdIds()->all())),
            ],
            'budget_year' => ['required', 'integer', 'min:2000', 'max:2200'],
            'work_type' => ['required', 'string', 'max:80'],
            'work_description' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:255'],
            'main_budget' => ['nullable', 'numeric', 'min:0'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*.name' => ['required', 'string', 'max:120'],
            'supporting_documents.*.amount' => ['required', 'numeric', 'min:0'],
            'execution_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:40'],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'achievement' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
    }

    private function accessibleOpds(User $user): Collection
    {
        return $user->isPicOpd()
            ? Opd::whereIn('id', $user->assignedOpdIds())->orderBy('name')->get()
            : Opd::orderBy('name')->get();
    }

    private function statuses(): array
    {
        return ['Tercantum Dalam DPA', 'Proses RUP', 'Proses Pengadaan', 'Proses Pekerjaan', 'Proses Pencairan', 'Selesai'];
    }

    private function supportingDocuments(array $documents): array
    {
        return collect($documents)
            ->map(fn (array $document) => [
                'document_name' => $document['name'],
                'amount' => $document['amount'],
            ])
            ->values()
            ->all();
    }
}
