<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CheckForAnyScope as BaseMiddleware;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions extends BaseMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return JsonResponse|\Illuminate\Http\Response|mixed
     * @throws AuthenticationException
     */
    public function handle($request, $next, ...$scopes): mixed
    {
        $permission = $request->route()->getName();

        if ($permission) {
            $user = auth()->guard('api')->user();

            if (! $user || ! $user->token()) {
                $code = Response::HTTP_UNAUTHORIZED;
                $output = [
                    'status' => false,
                    'message' => 'Unauthorized Entry.',
                    'code' => $code
                ];
                return response()->json($output, $code);
            }
        } else {
            $code = Response::HTTP_UNAUTHORIZED;
            $output = [
                'status' => false,
                'message' => 'Unknown Route name.',
                'code' => $code
            ];
            return response()->json($output, $code);
        }
        return $next($request);
    }
}
