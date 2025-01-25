<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the Authorization header
        $apiToken = $request->header('Authorization');

        // Check if the token exists and matches a user
        if (!$apiToken || !User::where('api_token', hash('sha256', $apiToken))->exists()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Allow the request to proceed
        return $next($request);
    }
}
