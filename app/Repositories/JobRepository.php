<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\ProfileUpdate;
use App\Models\Recruiter;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    /**
     * @param $request
     * @return array
     */
    public function getStats($request): array
    {
        $total_users = $total_verified_users = $total_ios_users = $total_android_users =  $total_web_users =
        $total_recruiter = $total_verified_recruiter = $total_ios_recruiter = $total_android_recruiter =
        $total_web_recruiter = $total_complete_profiles = 0;

        $total_users = User::selectRaw('count(id) As total_users')->first()->total_users;
        $total_verified_users = User::selectRaw('count(id) As total_verified_users')->where('verified', 1)->first()
            ->total_verified_users;

        $total_recruiter = Recruiter::selectRaw('count(id) As total_recruiter')->first()->total_recruiter;
        $total_verified_recruiter = Recruiter::selectRaw('count(id) As total_verified_recruiter')->where('verified', 1)->first()
            ->total_verified_recruiter;

        $total_complete_profiles = ProfileUpdate::selectRaw('count(user.id) As total_complete_profiles')
            ->join('user', 'user.id', 'profile_updates.user_id')
            ->where([
                'profile_updates.headline' => 1,
                'profile_updates.proffessional' => 1,
                'profile_updates.profile' => 1,
                'profile_updates.qualification' => 1,
                'profile_updates.video' => 1,
                'user.verified' => 1,
            ])
            ->first()->total_complete_profiles;

        $device_type_users = DB::select('SELECT device_type, COUNT(user_id) As user_count FROM (
            SELECT user_session.* FROM `user`
            JOIN user_session ON user_session.`user_id` = `user`.`id`
            GROUP BY user_session.`user_id` ORDER BY user_session.`id` DESC) asd
            GROUP BY device_type;');

        foreach ($device_type_users as $device_type_user) {
            if ($device_type_user->device_type == 'ios')
                $total_ios_users = $device_type_user->user_count;
            if ($device_type_user->device_type == 'android')
                $total_android_users = $device_type_user->user_count;
            if ($device_type_user->device_type == 'web')
                $total_web_users = $device_type_user->user_count;
        }

        $device_type_recruiters = DB::select('SELECT device_type, COUNT(rec_id) As recruiter_count FROM (
            SELECT recruiter_session.* FROM `recruiter`
            JOIN recruiter_session ON recruiter_session.`rec_id` = `recruiter`.`id`
            GROUP BY recruiter_session.`rec_id` ORDER BY recruiter_session.`id` DESC) asd
            GROUP BY device_type;');

        foreach ($device_type_recruiters as $device_type_recruiter) {
            if ($device_type_recruiter->device_type == 'ios')
                $total_ios_recruiter = $device_type_recruiter->recruiter_count;
            if ($device_type_recruiter->device_type == 'android')
                $total_android_recruiter = $device_type_recruiter->recruiter_count;
            if ($device_type_recruiter->device_type == 'web')
                $total_web_recruiter = $device_type_recruiter->recruiter_count;
        }

        return [
            'total_users'              => $total_users,
            'total_verified_users'     => $total_verified_users,
            'total_complete_profiles'  => $total_complete_profiles,
            'total_recruiter'          => $total_recruiter,
            'total_verified_recruiter' => $total_verified_recruiter,
            'total_ios_users'          => $total_ios_users,
            'total_android_users'      => $total_android_users,
            'total_web_users'          => $total_web_users,
            'total_ios_recruiter'      => $total_ios_recruiter,
            'total_android_recruiter'  => $total_android_recruiter,
            'total_web_recruiter'      => $total_web_recruiter,
        ];
    }
}
