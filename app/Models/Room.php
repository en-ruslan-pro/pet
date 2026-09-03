<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'pet_name',
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

    public static function createForPet(string $petName): self
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (self::query()->where('code', $code)->exists());

        return self::query()->create([
            'code' => $code,
            'pet_name' => $petName,
            'pet_needs_updated_at' => now(),
        ]);
    }

    /** @return array{hunger: int, energy: int, happiness: int} */
    public function petNeeds(): array
    {
        return [
            'hunger' => $this->hunger,
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

        $elapsedMinutes = $this->pet_needs_updated_at->diffInMinutes(now());

        if ($elapsedMinutes < 5) {
            return $this;
        }

        $hungerIncrease = intdiv($elapsedMinutes, 5);
        $energyDecrease = intdiv($elapsedMinutes, 10);
        $happinessDecrease = intdiv($elapsedMinutes, 15);

        $this->forceFill([
            'hunger' => min(100, $this->hunger + $hungerIncrease),
            'energy' => max(0, $this->energy - $energyDecrease),
            'happiness' => max(0, $this->happiness - $happinessDecrease),
            'pet_needs_updated_at' => now(),
        ])->save();

        return $this;
    }

    public function performPetAction(string $action): self
    {
        $this->refreshPetNeeds();

        $changes = match ($action) {
            'feed' => ['hunger' => max(0, $this->hunger - 30), 'happiness' => min(100, $this->happiness + 5)],
            'play' => ['hunger' => min(100, $this->hunger + 5), 'energy' => max(0, $this->energy - 15), 'happiness' => min(100, $this->happiness + 20)],
            'sleep' => ['hunger' => min(100, $this->hunger + 5), 'energy' => min(100, $this->energy + 35)],
        };

        $this->forceFill([
            ...$changes,
            'pet_needs_updated_at' => now(),
        ])->save();

        return $this;
    }

    public function isTvConnected(): bool
    {
        return $this->tv_connected_at?->isAfter(now()->subSeconds(30)) ?? false;
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
