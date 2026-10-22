<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResetLoginSessionController extends Controller
{
    public function __invoke(Request $request, string $destination): RedirectResponse
    {
        $route = match ($destination) {
            'internal' => 'login',
            'customer' => 'portal.login',
            default => abort(404),
        };

        Auth::guard(config('fortify.guard', 'web'))->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($route);
    }
}
