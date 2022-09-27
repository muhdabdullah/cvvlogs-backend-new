<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class BaseApiController
 * @package App\Http\Controllers
 */
class BaseApiController extends Controller
{
    /**
     * @param $result
     * @param $message
     * @param int $code
     * @return JsonResponseAlias
     */
    public function sendResponse($result, $message, int $code = Response::HTTP_OK): JsonResponseAlias
    {
        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => $message
        ], $code);
    }

    /**
     * @param array $errors
     * @param string $message
     * @param int $code
     * @param array $data
     * @return JsonResponseAlias
     */
    public function sendError(array $errors = [], string $message = 'Not Found', int $code = ResponseAlias::HTTP_NOT_FOUND, array $data = []): JsonResponseAlias
    {
        $dataErrors = [];
        if (!empty($errors)) {
            foreach ($errors as $key => $error) {
                $dataErrors[] = [
                    'label'   => $key,
                    'message' => $error
                ];
            }
        }
        return response()->json($this->makeError($message, $data, $dataErrors), $code);
    }

    /**
     * @param array $errors
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function sendErrorWithData(array $errors = [], int $code = 404, array $data = []): mixed
    {
        $dataErrors = [];
        if (!empty($errors)) {
            foreach ($errors as $key => $error) {
                $dataErrors[] = [
                    'label'   => $key,
                    'message' => $error
                ];
            }
        }
        /*if (empty($data)) {
            $data = ['errors' => $error];
        }*/
        return response()->json($this->makeError($dataErrors[0]['message'], $data, $dataErrors), $code);
    }

    /**
     * @param $message
     * @param array $data
     * @param array $errors
     * @return array
     */
    public static function makeError($message, array $data = [], array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ];
    }
}
