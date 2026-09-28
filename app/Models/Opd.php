<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_opd');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
