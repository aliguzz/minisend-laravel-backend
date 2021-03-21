<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IsShipper
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
        if ($request->user && $request->user->user_role == 1) {
            return $next($request);
        }
        throw new HttpResponseException(response()->json([
            'status'  => JsonResponse::HTTP_UNAUTHORIZED, 'error' => 'You don\'t have permissions to access this page'
        ], JsonResponse::HTTP_UNAUTHORIZED));
    }
}
