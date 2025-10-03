<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => null,
            'vehicle_type' => fake()->randomElement(Courier::VEHICLE_TYPES),
            'external_code' => fake()->optional()->bothify('EXT-####'),
            'active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Courier $courier) {
            $clientId = $courier->client_id ?? Client::factory()->create()->id;
            $courier->client_id = $clientId;

            if (! $courier->user_id) {
                $user = User::factory()->create([
                    'client_id' => $clientId,
                ]);

                $courier->user_id = $user->id;
                $courier->setRelation('user', $user);
            }
        })->afterCreating(function (Courier $courier) {
            $courier->loadMissing('user');

            if ($courier->user && $courier->user->client_id !== $courier->client_id) {
                $courier->user->forceFill(['client_id' => $courier->client_id])->save();
            }
        });
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn () => ['client_id' => $client->id]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'client_id' => $user->client_id,
            'user_id' => $user->id,
        ]);
    }
}
