<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'client_id' => Client::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'notes' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
