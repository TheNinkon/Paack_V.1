<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderBarcode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderBarcodeFactory extends Factory
{
    protected $model = ProviderBarcode::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'label' => fake()->unique()->words(2, true),
            'pattern_regex' => '^' . fake()->bothify('[A-Z0-9]{10}') . '$',
            'sample_code' => strtoupper(fake()->bothify('??########')),
            'priority' => fake()->numberBetween(1, 200),
            'active' => true,
        ];
    }
}
