<?php

namespace App\Models;

use Database\Factories\PetModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetModel extends Model
{
    /** @use HasFactory<PetModelFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'pet_type_id',
        'key',
        'name',
        'asset_path',
        'configuration',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    /** @return BelongsTo<PetType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PetType::class, 'pet_type_id');
    }

    /** @return BelongsToMany<PetAction, $this> */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(PetAction::class)
            ->withPivot(['execution_configuration', 'interaction_points', 'is_available'])
            ->withTimestamps();
    }

    /** @return HasMany<PetModelAction, $this> */
    public function petModelActions(): HasMany
    {
        return $this->hasMany(PetModelAction::class);
    }

    /** @return HasMany<PetModelAnimationStep, $this> */
    public function animationSteps(): HasMany
    {
        return $this->hasMany(PetModelAnimationStep::class);
    }

    /**
     * @return array<string, array{steps: list<array{key: string, durationSeconds: ?int, clips: list<array{name: string, weight: int, playbackRate: float, isLooping: bool}>}>, settings: array<string, mixed>}>
     */
    public function animationConfiguration(): array
    {
        $this->loadMissing([
            'animationSteps.animationStep',
            'animationSteps.clips',
            'petModelActions.petAction',
            'petModelActions.steps.animationStep',
        ]);

        $modelSteps = $this->animationSteps
            ->where('is_available', true)
            ->keyBy('pet_animation_step_id');

        $configuration = [];

        foreach ($this->petModelActions->where('is_available', true) as $action) {
            $steps = [];

            foreach ($action->steps->where('is_available', true) as $step) {
                $modelStep = $modelSteps->get($step->pet_animation_step_id);

                if (! $modelStep instanceof PetModelAnimationStep || $step->animationStep === null) {
                    continue;
                }

                $clips = [];

                foreach ($modelStep->clips as $clip) {
                    $clips[] = [
                        'name' => $clip->clip_name,
                        'weight' => $clip->weight,
                        'playbackRate' => $clip->playback_rate,
                        'isLooping' => $clip->is_looping,
                    ];
                }

                if ($clips === []) {
                    continue;
                }

                $steps[] = [
                    'key' => $step->animationStep->key,
                    'durationSeconds' => $step->duration_seconds,
                    'clips' => $clips,
                ];
            }

            if ($steps !== []) {
                $actionConfiguration = $action->petAction->getAttribute('configuration');
                $executionConfiguration = $action->getAttribute('execution_configuration');
                $interactionPoints = $action->getAttribute('interaction_points');

                $configuration[$action->petAction->key] = [
                    'steps' => $steps,
                    'settings' => [
                        ...(is_array($actionConfiguration) ? $actionConfiguration : []),
                        ...(is_array($executionConfiguration) ? $executionConfiguration : []),
                        'name' => $action->petAction->name,
                        'targetRoomItemKey' => is_array($interactionPoints) ? ($interactionPoints['room_item_key'] ?? null) : null,
                    ],
                ];
            }
        }

        return $configuration;
    }

    /**
     * @param  array<int, string>  $actionKeys
     * @return array<int, string>
     */
    public function missingActionAnimations(array $actionKeys): array
    {
        return array_values(array_diff($actionKeys, array_keys($this->animationConfiguration())));
    }

    /** @return HasMany<Pet, $this> */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    /** @return list<string> */
    public function animationClipNames(): array
    {
        $clipNames = [];

        foreach ($this->animationSteps()->with('clips')->get() as $step) {
            foreach ($step->clips as $clip) {
                $clipNames[] = $clip->clip_name;
            }
        }

        return array_values(array_unique($clipNames));
    }
}
