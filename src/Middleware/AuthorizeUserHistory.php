<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class AuthorizeUserHistory
{
    /**
     * Deny (404) unless the host app defines and grants the `viewUserHistory` gate.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Gate::allows('viewUserHistory'), 404);

        return $next($request);
    }
}
