<?php

namespace App\Repositories;

use App\Models\Job;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException as ValidatorExceptionAlias;

/**
 * Class SampleRepository
 * @package App\Data\Repositories
 */
class JobRepository extends BaseRepository
{
    /**
     * @return string
     */
    function model(): string
    {
        return Job::class;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAllJobs($request): mixed
    {
        $admin_status = $request['admin_status'] ?? '';
        return $this->model->select('id As job_id', 'recruiter_id', 'job_title')
            ->where('is_admin_approved', $admin_status)->orderBy('created_at', 'desc')
            ->with([
                'country:id,name',
                'city:id,name',
                'recruiter.company.industry:id,name'
            ])->withCount('applications As total_applicants')->paginate(15);
    }
}
