<?php

namespace App\Models;

use Database\Factories\RoomItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomItem extends Model
{
    /** @use HasFactory<RoomItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'room_id',
        'key',
        'name',
        'asset_path',
        'configuration',
        'interaction_points',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'interaction_points' => 'array',
        ];
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
