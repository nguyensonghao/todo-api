<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use App\Http\Helpers\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class UserLogin extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Token is invalid'
            ], 403);
        }

        return $next($request);
    }
}
