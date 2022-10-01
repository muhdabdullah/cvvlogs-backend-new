<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class Name DashboardController
 */
class DashboardController extends BaseApiController
{
    /**
     * @var JobRepository
     */
    private JobRepository $jobRepository;

    /**
     * @param JobRepository $jobRepository
     */
    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllJobs(Request $request): JsonResponse
    {
        $data = $this->jobRepository->getAllJobs($request->only(['admin_status']));
        return $this->sendResponse($data, __('response.success'));
    }
}
