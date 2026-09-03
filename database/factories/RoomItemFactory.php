<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomItem>
 */
class RoomItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'key' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'asset_path' => '/models/room/'.fake()->slug(2).'.glb',
            'configuration' => ['position' => [0, 0, 0]],
            'interaction_points' => ['default' => [0, 0, 0]],
        ];
    }
}
