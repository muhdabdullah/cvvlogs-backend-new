<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\UpdateUserVideoRequest;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    /**
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        $data = $this->jobRepository->getStats();
        return $this->sendResponse($data, __('response.success'));
    }

    /**
     * @return JsonResponse
     */
    public function getUserVideos(): JsonResponse
    {
        $data = $this->jobRepository->getUserVideos();
        return $this->sendResponse($data, __('response.success'));
    }

    /**
     * @param UpdateUserVideoRequest $request
     * @return JsonResponse
     */
    public function updateVideoStatus(UpdateUserVideoRequest $request): JsonResponse
    {
        $data = $this->jobRepository->updateVideoStatus($request->only(['id', 'status']));
        return $this->sendResponse($data, __('response.update'));
    }
}
