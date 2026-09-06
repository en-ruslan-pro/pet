<?php

namespace App\Models;

use Database\Factories\PetActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetAction extends Model
{
    /** @use HasFactory<PetActionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'key',
        'name',
        'configuration',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    /** @return array<int, string> */
    public static function baseActionKeys(): array
    {
        return static::query()
            ->orderBy('key')
            ->pluck('key')
            ->map(static fn (mixed $key): string => (string) $key)
            ->values()
            ->all();
    }

    /** @return BelongsToMany<PetModel, $this> */
    public function models(): BelongsToMany
    {
        return $this->belongsToMany(PetModel::class)
            ->withPivot(['execution_configuration', 'interaction_points', 'is_available'])
            ->withTimestamps();
    }

    /** @return HasMany<PetModelAction, $this> */
    public function petModelActions(): HasMany
    {
        return $this->hasMany(PetModelAction::class);
    }
}
