<?php

namespace App\Http\Controllers\Api\Admin;

use App\Criteria\JobFilterCriteria;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\ListJobsRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Prettus\Repository\Exceptions\RepositoryException;

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
     * @param ListJobsRequest $request
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function getAllJobs(ListJobsRequest $request): JsonResponse
    {
        $data = $this->jobRepo->resetCriteria()->pushCriteria(new JobFilterCriteria($request))->paginate($request->limit);
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
