<?php

Namespace Simplydigital\NotificationManagement\Middleware;
use Closure;
use Illuminate\Http\Request;

class RestrictInternalUserOnly
{
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