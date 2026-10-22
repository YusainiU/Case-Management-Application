<?php

namespace Simplydigital\CaseManagement\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCustomerPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('portal.login'));
        }

        abort_unless($request->user()->can('portal.access'), 403);

        return $next($request);
    }
}
