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

    public function fetchJobs()
    {
        $xml = XmlParser::load('https://www.recruitwell.com/wp-content/uploads/doccafe.xml');
        $jobs = [];
        foreach ($xml->getContent() as $xml) {
            $jobs[] = $this->simplexml2array($xml);
        }
        $recruiter = Recruiter::where('email', 'alamb@recruitwell.com')->value('id');
        if (!$recruiter) {
            $recruiter = DB::table('recruiter')->insertGetId([
                'name' => 'Recruitwell',
                'email' => 'alamb@recruitwell.com',
                'num' => '+920000000000',
                'num_code' => '+92',
                'password' => 'qwerty',
                'premium' => 1,
                'company_name' => 'Recruitwell',
                'verified' => 1,
                'deleted' => 0,
            ]);
        }
        foreach ($jobs as $job) {
            $city = City::where('name', $job['city'])->first();
            if ($city) {
                $jobData = [
                    'job_title' => $job['jobtitle'],
                    'job_description' => $job['jobdescription'],
                    'recruiter_id' => $recruiter,
                    'country_id' => $city->country_id,
                    'city_id' => $city->id,
                    'state_id' => $city->state_id,
                    'salary_type' => 1,
                    'salary_min' => 100,
                    'salary_max' => 10000,
                    'gender' => 'Both',
                    'currency' => 'PKR',
                    'experience_req' => 1,
                    'work_level' => 1,
                    'international_hiring_status' => 0,
                    'inter_hiring_country' => NULL,
                    'status' => 1,
                    'vacancy' => 1,
                    'is_admin_approved' => 0,
                    'job_type' => 'recruitwell',
                ];
                DB::table('job')->insert($jobData);
            }
        }

        return $this->sendResponse([], __('response.success'));
    }

    function simplexml2array($xml) {
        if(is_object($xml) && get_class($xml) == 'SimpleXMLElement') {
            $attributes = $xml->attributes();
            foreach($attributes as $k=>$v) {
                $a[$k] = (string) $v;
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }

        if(is_array($xml)) {
            if(count($xml) == 0) {
                return (string) $x;
            }
            $r = array();
            foreach($xml as $key=>$value) {
                $r[$key] = $this->simplexml2array($value);
            }
            // Ignore attributes
            if (isset($a)) {
                $r['@attributes'] = $a;
            }
            return $r;
        }
        return (string) $xml;
    }
}
