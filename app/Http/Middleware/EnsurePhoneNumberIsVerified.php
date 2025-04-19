<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\MustVerifyPhoneNumber;

class EnsurePhoneNumberIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyPhoneNumber &&
            ! $request->user()->hasVerifiedPhoneNumber())) {
            return response()->json(['message' => 'Your phone number is not verified.'], 409);
        }
        return $next($request);
    }
}
