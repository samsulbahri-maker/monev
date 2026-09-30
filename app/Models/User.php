<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'opd_id', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function opds(): BelongsToMany
    {
        return $this->belongsToMany(Opd::class, 'user_opd');
    }

    public function isPicOpd(): bool
    {
        return $this->role === 'pic_opd';
    }

    public function canManageProposals(): bool
    {
        return $this->role !== 'viewer';
    }

    public function assignedOpdIds(): Collection
    {
        return $this->opds()->pluck('opds.id')
            ->when($this->opd_id, fn (Collection $ids) => $ids->push($this->opd_id))
            ->unique()
            ->values();
    }
}
