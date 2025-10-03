<?php

namespace App\Http\Middleware;

use App\Models\CourierToken;
use App\Support\ClientContext;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EnsureCourierTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        $plainToken = $this->extractToken($request);

        if (! $plainToken) {
            return $this->unauthorized(__('Token no proporcionado.'));
        }

        $hashed = hash('sha256', $plainToken);

        /** @var CourierToken|null $token */
        $token = CourierToken::with('user')->where('token', $hashed)->first();

        if (! $token || ! $token->user || ! $token->user->is_active) {
            return $this->unauthorized(__('Token inválido.'));
        }

        if ($token->expires_at instanceof CarbonInterface && $token->expires_at->isPast()) {
            return $this->unauthorized(__('Token expirado.'));
        }

        if (! $token->user->hasRole('courier')) {
            return $this->unauthorized(__('Acceso no autorizado.'));
        }

        app(ClientContext::class)->setClient($token->user->client);

        Auth::setUser($token->user);
        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('courier_token', $token);

        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization');

        if ($authorization && Str::startsWith($authorization, 'Bearer ')) {
            return trim(Str::after($authorization, 'Bearer '));
        }

        return $request->header('X-Courier-Token') ?? $request->query('token');
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }
}
