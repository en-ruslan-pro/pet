<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon|null $tv_connected_at
 * @property Carbon|null $pet_needs_updated_at
 */
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    /** @var array{satiety_per_minutes: int, energy_per_minutes: int, happiness_per_minutes: int} */
    public const NEED_DECAY = [
        'satiety_per_minutes' => 5,
        'energy_per_minutes' => 10,
        'happiness_per_minutes' => 15,
    ];

    /** @var list<string> */
    protected $fillable = [
        'code',
        'pet_name',
        'character_id',
        'tv_connected_at',
        'hunger',
        'energy',
        'happiness',
        'pet_needs_updated_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tv_connected_at' => 'datetime',
            'pet_needs_updated_at' => 'datetime',
        ];
    }

    public static function createForCharacter(Character $character, ?string $petName = null): self
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (self::query()->where('code', $code)->exists());

        return self::query()->create([
            'code' => $code,
            'character_id' => $character->id,
            'pet_name' => filled($petName) ? $petName : $character->default_name,
            'pet_needs_updated_at' => now(),
        ]);
    }

    /** @return array{satiety: int, energy: int, happiness: int} */
    public function petNeeds(): array
    {
        return [
            'satiety' => 100 - $this->hunger,
            'energy' => $this->energy,
            'happiness' => $this->happiness,
        ];
    }

    public function refreshPetNeeds(): self
    {
        if ($this->pet_needs_updated_at === null) {
            $this->forceFill(['pet_needs_updated_at' => now()])->save();

            return $this;
        }

        $elapsedWholeMinutes = (int) floor($this->pet_needs_updated_at->diffInMinutes(now()));

        if ($elapsedWholeMinutes < 5) {
            return $this;
        }

        $hungerIncrease = intdiv($elapsedWholeMinutes, self::NEED_DECAY['satiety_per_minutes']);
        $energyDecrease = intdiv($elapsedWholeMinutes, self::NEED_DECAY['energy_per_minutes']);
        $happinessDecrease = intdiv($elapsedWholeMinutes, self::NEED_DECAY['happiness_per_minutes']);

        $this->forceFill([
            'hunger' => min(100, $this->hunger + $hungerIncrease),
            'energy' => max(0, $this->energy - $energyDecrease),
            'happiness' => max(0, $this->happiness - $happinessDecrease),
            'pet_needs_updated_at' => now(),
        ])->save();

        return $this;
    }

    /** @param array<string, int|float> $needEffects */
    public function performPetAction(string $action, array $needEffects = []): self
    {
        $this->refreshPetNeeds();

        if ($needEffects !== []) {
            return $this->applyNeedEffects($needEffects);
        }

        $changes = match ($action) {
            'feed' => ['satiety' => min(100, $this->petNeeds()['satiety'] + 8)],
            'play' => ['satiety' => max(0, $this->petNeeds()['satiety'] - 4), 'energy' => max(0, $this->energy - 6), 'happiness' => min(100, $this->happiness + 8)],
            'sleep' => ['satiety' => max(0, $this->petNeeds()['satiety'] - 3), 'energy' => min(100, $this->energy + 8), 'happiness' => max(0, $this->happiness - 4)],
            default => throw new \InvalidArgumentException("Unsupported pet action: {$action}"),
        };

        $this->forceFill([
            'hunger' => 100 - $changes['satiety'],
            'energy' => $changes['energy'] ?? $this->energy,
            'happiness' => $changes['happiness'] ?? $this->happiness,
            'pet_needs_updated_at' => now(),
        ])->save();

        return $this;
    }

    /** @param array<string, int|float> $needEffects */
    public function applyNeedEffects(array $needEffects): self
    {
        $changes = [];

        foreach ($this->petNeeds() as $need => $value) {
            $changes[$need] = (int) max(0, min(100, $value + ($needEffects[$need] ?? 0)));
        }

        $this->forceFill([
            'hunger' => 100 - $changes['satiety'],
            'energy' => $changes['energy'],
            'happiness' => $changes['happiness'],
            'pet_needs_updated_at' => now(),
        ])->save();

        return $this;
    }

    public function isTvConnected(): bool
    {
        return $this->tv_connected_at?->isAfter(now()->subSeconds(30)) ?? false;
    }

    /** @return HasMany<Pet, $this> */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    /** @return BelongsTo<Character, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /** @return HasMany<RoomItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RoomItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
