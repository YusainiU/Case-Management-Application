<?php

Namespace Simplydigital\CaseManagement\Middleware;
use Closure;
use Illuminate\Http\Request;

class RestrictInternalUserOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if(!$user) {
            abort(403, 'Unauthorized');
        }
        if($user->isInternalUser()) {
            return $next($request);
        }        
        abort(403, 'Unauthorized');
    }
}