<?php

namespace App\Criteria;

use App\Models\User;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class JobFilterCriteria.
 *
 * @package namespace App\Criteria;
 */
class JobFilterCriteria implements CriteriaInterface
{
    protected Request $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository): mixed
    {
        $userId = $this->request->get("user_id", false);
        if ($userId > 0) {
            $userCountry = User::where('id', $userId)->value('country');
            if ($userCountry > 0) {
                $model = $model->when(($userCountry != ''), function ($query) use ($userCountry) {
                    return $query->where('country_id', $userCountry);
                });
            }
        }

        $keyword = $this->request->get("keyword", '');
        $model = $model->when(($keyword != ''), function ($query) use ($keyword) {
            $keyword = str_replace(' ', '%" OR "%', $keyword);
            return $query->where(function($q) use($keyword) {
                $q->whereRaw('job_title LIKE "%'.$keyword.'%"');
                //$q->orWhere('job_title', 'LIKE', '%' . $keyword);
                //$q->orWhere('job_title', 'LIKE', $keyword . '%');
            });
        });

        $country_id = $this->request->get("country_id", []);
        $model = $model->when(($country_id != []), function ($query) use ($country_id) {
            return $query->whereIn('country_id', $country_id);
        });

        $state_id = $this->request->get("state_id", []);
        $model = $model->when(($state_id != []), function ($query) use ($state_id) {
            return $query->whereIn('state_id', $state_id);
        });

        $city_id = $this->request->get("city_id", []);
        $model = $model->when(($city_id != []), function ($query) use ($city_id) {
            return $query->whereIn('city_id', $city_id);
        });

        $industry_id = $this->request->get("industry_id", []);
        $model = $model->when(($industry_id != []), function ($query) use ($industry_id) {
            return $query->join('job_industry', 'job.id', 'job_industry.job_id')
                ->whereIn('job_industry.industry_id', $industry_id);
        });

        $work_level_id = $this->request->get("work_level_id", []);
        $model = $model->when(($work_level_id != []), function ($query) use ($work_level_id) {
            return $query->whereIn('work_level', $work_level_id);
        });

        $admin_status = $this->request->get("admin_status", '');
        $model = $model->when(($admin_status != ''), function ($query) use ($admin_status) {
            return $query->where('is_admin_approved', $admin_status);
        });

        $featured = $this->request->get("featured", '');
        $model = $model->when(($featured != ''), function ($query) use ($featured) {
            return $query->where('is_featured', $featured);
        });

        $min_salary = $this->request->get("min_salary", '');
        $model = $model->when(($min_salary != ''), function ($query) use ($min_salary) {
            return $query->where('salary_min', '>', $min_salary);
        });

        $max_salary = $this->request->get("max_salary", '');
        $model = $model->when(($max_salary != ''), function ($query) use ($max_salary) {
            return $query->where('salary_max', '<', $max_salary);
        });

        $is_fav = $this->request->get("is_fav", '');
        $model = $model->when(in_array($is_fav, [1]), function ($query) use ($is_fav) {
            return $query->join('user_fav_jobs', 'user_fav_jobs.job_id', 'job.id');
        });

        return $model->select('job.*')->orderBy('created_at', 'desc')
            ->with([
                'country:id,name',
                'state:id,name',
                'city:id,name',
                'industry:id,name',
                'workLevel:id,name',
                'experience:id,name',
                'functional_area:id,name',
                'skill:id,name',
                'recruiter:id,name,email,num,company_name',
                'recruiter.company.industry:id,name'
            ])->withCount('applications As total_applicants');
    }
}
