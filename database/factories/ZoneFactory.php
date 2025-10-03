<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => 'Zona ' . fake()->unique()->citySuffix(),
            'code' => Str::upper(fake()->unique()->lexify('???')),
            'notes' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
