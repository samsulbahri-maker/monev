<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory;

    public const STATUSES = [
        'Tercantum Dalam DPA',
        'Proses RUP',
        'Proses Pengadaan',
        'Proses Pekerjaan',
        'Proses Pencairan',
        'Selesai',
    ];

    public const WORK_TYPES = [
        'Fisik Konstruksi',
        'Fisik Non Konstruksi',
        'Non Fisik Non Konstruksi',
    ];

    protected $fillable = [
        'program_id', 'opd_id', 'created_by', 'budget_year', 'work_type',
        'work_description', 'location', 'map_url', 'main_budget',
        'supporting_documents', 'supporting_budget', 'execution_date',
        'status', 'progress_percentage', 'achievement', 'evidence_path',
    ];

    protected function casts(): array
    {
        return [
            'supporting_documents' => 'array',
            'execution_date' => 'date',
            'main_budget' => 'decimal:2',
            'supporting_budget' => 'decimal:2',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function supportingDocumentItems(): HasMany
    {
        return $this->hasMany(ProposalSupportingDocument::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $user->isPicOpd()
            ? $query->whereIn('opd_id', $user->assignedOpdIds())
            : $query;
    }

    public function isAccessibleTo(User $user): bool
    {
        return ! $user->isPicOpd() || $user->assignedOpdIds()->contains($this->opd_id);
    }
}
