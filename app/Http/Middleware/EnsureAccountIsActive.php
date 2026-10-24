<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_permanently_banned && $user->banned_until && $user->banned_until->isPast()) {
            $user->forceFill([
                'banned_until' => null,
                'ban_violation' => null,
                'ban_reason' => null,
            ])->save();
        }

        if ($user?->isAccountBanned()) {
            return redirect()->route('account.suspended');
        }

        return $next($request);
    }
}
