<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_name' => 'default',
            'description' => fake()->sentence(),
            'event' => fake()->randomElement(['created', 'updated', 'deleted']),
            'subject_type' => \App\Models\Client::class,
            'subject_id' => 1,
            'properties' => [],
        ];
    }
}
