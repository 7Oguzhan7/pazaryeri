<?php

namespace App\Http\Middleware;

use App\Models\Seller;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->hasRole('seller')
                && $user->seller?->status === Seller::STATUS_APPROVED,
            Response::HTTP_FORBIDDEN,
        );

        return $next($request);
    }
}
