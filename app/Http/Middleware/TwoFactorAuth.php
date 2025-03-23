<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated but hasn't completed 2FA
        if (auth()->check() && !Session::has('two_factor_authenticated')) {
            // Store intended URL in session
            Session::put('url.intended', url()->current());
            
            // Redirect to 2FA verification page
            return redirect()->route('two-factor.show');
        }

        return $next($request);
    }
}