<?php

namespace App\Models;

use Database\Factories\PetBalanceVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetBalanceVersion extends Model
{
    /** @use HasFactory<PetBalanceVersionFactory> */
    use HasFactory;

    protected $fillable = ['pet_model_id', 'configuration_hash', 'configuration', 'published_at'];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'published_at' => 'datetime'];
    }

    /** @return BelongsTo<PetModel, $this> */
    public function petModel(): BelongsTo
    {
        return $this->belongsTo(PetModel::class);
    }

    /** @return HasMany<PetActionExecution, $this> */
    public function executions(): HasMany
    {
        return $this->hasMany(PetActionExecution::class);
    }
}
