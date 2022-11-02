<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\DashboardStatsRequest;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class Name DashboardController
 */
class DashboardApiController extends BaseApiController
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
     * @param DashboardStatsRequest $request
     * @return JsonResponse
     */
    public function getStats(DashboardStatsRequest $request): JsonResponse
    {
        $data = $this->jobRepository->getStats($request->only(['start_date', 'end_date']));
        return $this->sendResponse($data, __('response.success'));
    }

    /**
     * @param DashboardStatsRequest $request
     * @return JsonResponse
     */
    public function getMonthlyStats(DashboardStatsRequest $request): JsonResponse
    {
        $data = $this->jobRepository->getMonthlyStats($request->only(['start_date', 'end_date']));
        return $this->sendResponse($data, __('response.success'));
    }
}
