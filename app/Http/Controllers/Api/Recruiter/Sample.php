<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\BaseApiController;
use App\Models\recruiterSession;
use Illuminate\Http\JsonResponse as JsonResponseAlias;

class Sample extends BaseApiController
{
    /**
     * @return JsonResponseAlias
     */
    public function index(): JsonResponseAlias
    {
        $data = recruiterSession::all();
        return $this->sendResponse($data, __('response.success'));
    }
}
