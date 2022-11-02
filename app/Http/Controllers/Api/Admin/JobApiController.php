<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApiController extends BaseApiController
{
    /**
     * @var JobRepository
     */
    protected JobRepository $jobRepo;

    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepo = $jobRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllJobs(Request $request): JsonResponse
    {
        $data = $this->jobRepo->getAllJobs($request->only(['admin_status']));
        return $this->sendResponse($data, __('response.success'));
    }

    /**
     * @param UpdateJobStatusRequest $request
     * @return JsonResponse
     */
    public function updateJobApproveStatus(UpdateJobStatusRequest $request): JsonResponse
    {
        $data = $this->jobRepo->updateJobStatus($request->only(['id', 'status']));
        return $this->sendResponse($data, __('response.update'));
    }
}
