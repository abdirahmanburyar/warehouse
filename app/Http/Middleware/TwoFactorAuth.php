<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            // Check for trusted device cookie
            $trustedToken = $request->cookie('trusted_device_token');
            $user = Auth::user();
            Log::info('2FA Middleware: Checking trusted device', [
                'user_id' => $user ? $user->id : null,
                'trustedToken' => $trustedToken,
                'session_has_2fa' => Session::has('two_factor_authenticated'),
            ]);
            if ($trustedToken && $user) {
                $trustedDevice = TrustedDevice::where('user_id', $user->id)
                    ->where('device_token', $trustedToken)
                    ->first();
                if ($trustedDevice) {
                    Session::put('two_factor_authenticated', true);
                    $request->session()->save(); // Ensure session is saved!
                    Log::info('2FA Middleware: Trusted device found, session set', [
                        'user_id' => $user->id,
                        'device_token' => $trustedToken,
                    ]);
                    $trustedDevice->update(['last_used_at' => now()]);
                    return $next($request);
                }
            }
            // Store intended URL in session
            Session::put('url.intended', url()->current());
            
            // Redirect to 2FA verification page
            return redirect()->route('two-factor.show');
        }

        Log::info('2FA Middleware: Passing through', [
            'session_has_2fa' => Session::has('two_factor_authenticated'),
        ]);
        return $next($request);
    }
}