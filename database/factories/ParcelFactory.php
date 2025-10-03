<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Parcel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parcel>
 */
class ParcelFactory extends Factory
{
    protected $model = Parcel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'code' => strtoupper(fake()->bothify('PKG###########')),
            'stop_code' => fake()->optional()->bothify('STOP-####'),
            'address_line' => fake()->streetAddress(),
            'latitude' => (float) fake()->latitude(),
            'longitude' => (float) fake()->longitude(),
            'formatted_address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'liquidation_code' => fake()->optional()->bothify('L########'),
            'liquidation_reference' => fake()->optional()->bothify('REF-########'),
            'status' => 'pending',
        ];
    }
}
