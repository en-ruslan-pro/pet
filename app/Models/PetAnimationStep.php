<?php

namespace App\Models;

use Database\Factories\PetAnimationStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetAnimationStep extends Model
{
    /** @use HasFactory<PetAnimationStepFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['key', 'name'];

    /** @return HasMany<PetModelAnimationStep, $this> */
    public function modelSteps(): HasMany
    {
        return $this->hasMany(PetModelAnimationStep::class);
    }

    /** @return HasMany<PetModelActionStep, $this> */
    public function actionSteps(): HasMany
    {
        return $this->hasMany(PetModelActionStep::class);
    }
}
