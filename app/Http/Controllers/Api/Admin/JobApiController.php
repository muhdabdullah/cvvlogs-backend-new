<?php

namespace App\Http\Controllers\Api\Admin;

use App\Criteria\JobFilterCriteria;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\ListJobsRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Models\Application;
use App\Models\City;
use App\Models\Job;
use App\Models\Recruiter;
use App\Repositories\JobRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Prettus\Repository\Exceptions\RepositoryException;
use Orchestra\Parser\Xml\Facade as XmlParser;

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

    public function markJobApplication($id, Request $request)
    {
        $user = auth()->guard('api')->user();
        if ($user && $request->has('come_from')) {
            Application::updateOrCreate(['job_id' => $id, 'user_id' => $user->id], ['job_id' => $id, 'user_id' => $user->id, 'come_from' => $request->come_from]);
        }
        return $this->sendResponse([], __('response.update'));
    }
}
