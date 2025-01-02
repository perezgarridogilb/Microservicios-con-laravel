<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            abort(response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED));
        }
        /* return $request->expectsJson() ? null : route('login'); */
    }
}
