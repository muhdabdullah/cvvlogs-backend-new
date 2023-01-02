<?php

namespace App\Http\Controllers\Api\Admin;

use App\Criteria\JobFilterCriteria;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\ListJobsRequest;
use App\Http\Requests\Admin\MarkUnMarkJobFavRequest;
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

        $date = strtotime(date("Y-m-d h:i:sa"));

        foreach ($data as $datum) {
            $datum->title = $datum->job_title;
            $datum->description = $datum->job_description;
            $datum->rec = @$datum->recruiter->company_name;
            $datum->exp = @$datum->experience->name;
            $datum->country = @$datum->country->name;
            $datum->state = @$datum->state->name;
            $datum->city = @$datum->city->name;
            $datum->func = @$datum->functional_area[0]->name;


            $start = strtotime($datum->created_at);
            $datediff = $date - $start;
            $ago = round($datediff / (60 * 60 * 24));
            if($ago == 0)
                $datum->ago = "few hours";
            else
                $datum->ago = $ago.' days';
        }

        if (isset($request->user_id) && $request->keyword) {
            $where = ['user_id' => $request->user_id, 'keyword' => $request->keyword];
            DB::table('user_search_logs')->updateOrInsert($where,[
                'user_id' => $request->user_id, 'keyword' => $request->keyword, 'count' => 1
            ]);
        }

        return $this->sendResponse($data, __('response.success'));
    }

    public function markUnmarkJobFav(MarkUnMarkJobFavRequest $request)
    {
        if ($request->is_fav == 1) {
            DB::table('user_fav_jobs')->updateOrInsert(['job_id' => $request->job_id, 'user_id' => $request->user_id],['job_id' => $request->job_id, 'user_id' => $request->user_id]);
        } else {
            DB::table('user_fav_jobs')->where(['job_id' => $request->job_id, 'user_id' => $request->user_id])->delete();
        }
        return $this->sendResponse([], __('response.success'));
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
