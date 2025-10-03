<?php

namespace App\Http\Controllers\Api\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->active()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => __('Credenciales inválidas.'),
            ], 422);
        }

        if (! $user->hasRole('courier')) {
            return response()->json([
                'message' => __('Este usuario no tiene rol de repartidor.'),
            ], 403);
        }

        $user->loadMissing('courier');

        if ($user->courier && ! $user->courier->active) {
            return response()->json([
                'message' => __('El perfil de repartidor está inactivo.'),
            ], 403);
        }

        $plainToken = Str::random(80);
        $token = CourierToken::create([
            'user_id' => $user->id,
            'name' => $credentials['device_name'] ?? $request->userAgent(),
            'token' => hash('sha256', $plainToken),
            'abilities' => ['parcels:read', 'parcels:update'],
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at?->toIso8601String(),
            'user' => $this->transformUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var CourierToken|null $token */
        $token = $request->attributes->get('courier_token');

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => __('Sesión cerrada correctamente.'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->transformUser($user),
        ]);
    }

    private function transformUser(User $user): array
    {
        $courierProfile = $user->courier;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'client_id' => $user->client_id,
            'courier' => $courierProfile ? [
                'id' => $courierProfile->id,
                'vehicle_type' => $courierProfile->vehicle_type,
                'external_code' => $courierProfile->external_code,
                'active' => $courierProfile->active,
            ] : null,
        ];
    }
}
