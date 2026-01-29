<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPaymentPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if payment password is verified in session
        $sessionKey = 'payment_summary_verified_at';
        $timeout = config('payment.session_timeout', 30); // minutes

        if (!session()->has($sessionKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Password diperlukan untuk mengakses data ini.',
                'requires_password' => true,
            ], 403);
        }

        // Check if session is expired
        $verifiedAt = session($sessionKey);
        $expiresAt = $verifiedAt->addMinutes($timeout);

        if (now()->greaterThan($expiresAt)) {
            session()->forget($sessionKey);
            return response()->json([
                'success' => false,
                'message' => 'Session telah kadaluarsa. Silakan masukkan password lagi.',
                'requires_password' => true,
            ], 403);
        }

        return $next($request);
    }
}
