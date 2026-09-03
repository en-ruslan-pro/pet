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
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tv_connected_at' => 'datetime',
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
        ]);
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
