<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Tokens;

class IsAuthenticated
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
        // check if request headers has token
        if($request->headers->has('Authorization')) {
            $token = $request->header('Authorization');
            if(!empty($token)) {
                $tokenData = Tokens::where(['api_token' => $token])->first();
                if($tokenData) {
                    $request->merge(['user' => $tokenData->user]);
                    return $next($request);
                }
            }
        }
        throw new HttpResponseException(response()->json([
            'status'  => JsonResponse::HTTP_UNAUTHORIZED, 'error' => 'You don\'t have permissions to access this page'
        ], JsonResponse::HTTP_UNAUTHORIZED));
    }
}
