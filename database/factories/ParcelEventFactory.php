<?php

namespace Database\Factories;

use App\Models\Parcel;
use App\Models\ParcelEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParcelEvent>
 */
class ParcelEventFactory extends Factory
{
    protected $model = ParcelEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parcel_id' => Parcel::factory(),
            'code' => strtoupper(fake()->bothify('PKG-####')),
            'event_type' => fake()->randomElement(['created', 'assigned', 'status_changed']),
            'description' => fake()->optional()->sentence(),
            'payload' => null,
        ];
    }
}
