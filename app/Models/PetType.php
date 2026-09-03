<?php

namespace App\Models;

use Database\Factories\PetTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetType extends Model
{
    /** @use HasFactory<PetTypeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'key',
        'name',
        'needs_configuration',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'needs_configuration' => 'array',
        ];
    }

    /** @return HasMany<PetModel, $this> */
    public function models(): HasMany
    {
        return $this->hasMany(PetModel::class);
    }
}
