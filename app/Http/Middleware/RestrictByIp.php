<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RestrictByIp
{
    /**
     * The allowed IP address.
     */
    private string $allowedIp = '89.212.6.13';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->ip() !== $this->allowedIp) {
            abort(403, 'Access denied. Your IP is not whitelisted.');
        }

        return $next($request);
    }
}
