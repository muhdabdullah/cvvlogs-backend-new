<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    use AuthenticatesUsers;

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return string
     */
    public function username(): string
    {
        return 'username';
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Response
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse|Response|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            $this->username() => 'required|string',
            'password'        => 'required|string',
        ]);

        if ($validator->fails()) {
            $code = Response::HTTP_NOT_ACCEPTABLE;
            $output = ['error' => ['code' => $code, 'message' => $validator->errors()->first()]];
            return response()->json($output, $code);
        }
        if (auth()->attempt($request->only([$this->username(), 'password']))) {
            $token = auth()->user()->createToken('Auth-token')->accessToken;
            return response()->json(['access_token' => $token, 'user' => auth()->user()], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Unauthorised'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
