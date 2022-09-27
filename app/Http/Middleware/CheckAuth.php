<?php

namespace App\Http\Middleware;

use App\Models\recruiterSession;
use App\Models\userSession;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Response as ResponseAlias;
use Laravel\Passport\Http\Middleware\CheckForAnyScope as BaseMiddleware;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth extends BaseMiddleware
{
    /**
     * @param $request
     * @param $next
     * @param ...$scopes
     * @return JsonResponseAlias|ResponseAlias|mixed
     */
    public function handle($request, $next, ...$scopes): mixed
    {
        $authId = $request->header('auth-id', null);
        $segment = $request->segment(3);
        $status = 0;
        if ($segment == 'recruiter')
        {
            $status = recruiterSession::select('status')->where('token', $authId)->first()?->status;
        }
        if ($segment == 'user')
        {
            $status = userSession::select('status')->where('token', $authId)->first()?->status;
        }

        if (!$status) {
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
