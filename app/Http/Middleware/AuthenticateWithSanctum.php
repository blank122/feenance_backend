<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;

class AuthenticateWithSanctum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    private function findToken($token)
    {
        $hashedToken = hash('sha256', $token);

        return DB::table('personal_access_token')
            ->where('token', $hashedToken)
            ->exists();
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || !$this->findToken($token)) {
            // Token not found or invalid
            return response()->json(['message' => 'Unauthenticated Request.'], 401);
        }

        // Token found and valid, proceed with the request
        return $next($request);
    }
}
