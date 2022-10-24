<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\ProfileUpdate;
use App\Models\Recruiter;
use App\Models\User;
use App\Models\UserVideo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

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
        $start_date = isset($request['start_date'])? Carbon::parse($request['start_date'])->format('Y-m-d') :'';
        $end_date = isset($request['end_date'])? Carbon::parse($request['end_date'])->format('Y-m-d') :'';

        $total_users = $total_verified_users = $total_ios_users = $total_android_users =  $total_web_users =
        $total_recruiter = $total_verified_recruiter = $total_ios_recruiter = $total_android_recruiter =
        $total_web_recruiter = $total_complete_profiles = 0;

        $total_users = User::selectRaw('count(id) As total_users')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(`user`.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })->first()->total_users;

        $total_verified_users = User::selectRaw('count(id) As total_verified_users')->where('verified', 1)
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(user.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })->first()
            ->total_verified_users;

        $total_recruiter = Recruiter::selectRaw('count(id) As total_recruiter')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(recruiter.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })->first()->total_recruiter;
        $total_verified_recruiter = Recruiter::selectRaw('count(id) As total_verified_recruiter')->where('verified', 1)
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(recruiter.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })
            ->first()->total_verified_recruiter;

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
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(user.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })
            ->first()->total_complete_profiles;

        if ($start_date !== '') {
            $userWhereRaw = 'WHERE date(`user`.created_at) BETWEEN "'.$start_date.'" AND "'.$end_date.'"';
            $recruiterWhereRaw = 'WHERE date(`recruiter`.created_at) BETWEEN "'.$start_date.'" AND "'.$end_date.'"';
        } else
            $userWhereRaw = $recruiterWhereRaw = '';

        $device_type_users = DB::select('SELECT device_type, COUNT(user_id) As user_count FROM (
            SELECT user_session.* FROM `user`
            JOIN user_session ON user_session.`user_id` = `user`.`id` '.$userWhereRaw.'
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
            JOIN recruiter_session ON recruiter_session.`rec_id` = `recruiter`.`id` '.$recruiterWhereRaw.'
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

    /**
     * @return array
     */
    public function getUserVideos(): array
    {
        return ProfileUpdate::selectRaw('user_videos.id, user.id AS user_id, user.first_name, user.last_name, user.num, user_videos.link')
            ->selectRaw('IF(user_videos.status=0, "'.UserVideo::StatusText[UserVideo::PENDING].'", IF(user_videos.status='.UserVideo::PENDING.', "'.UserVideo::StatusText[UserVideo::PENDING].'", IF(user_videos.status='.UserVideo::APPROVED.', "'.UserVideo::StatusText[UserVideo::APPROVED].'", IF(user_videos.status='.UserVideo::REJECTED.', "'.UserVideo::StatusText[UserVideo::REJECTED].'", "")))) As status')
            ->join('user', 'user.id', 'profile_updates.user_id')
            ->join('user_videos', 'user_videos.user_id', 'profile_updates.user_id')
            ->where([
                'profile_updates.headline' => 1,
                'profile_updates.proffessional' => 1,
                'profile_updates.profile' => 1,
                'profile_updates.qualification' => 1,
                'profile_updates.video' => 1,
                'user.verified' => 1,
            ])->orderBy('user_videos.id', 'desc')->get()->toArray();
    }

    /**
     * Update User's Uploaded Video Status To Approved Or Reject
     *
     * @param $request
     * @return bool
     */
    public function updateVideoStatus($request): bool
    {
        return UserVideo::where('id', $request['id'])->update(['status' => $request['status']]);
    }

    /**
     * @param $request
     * @return array
     */
    public function getMonthlyStats($request): array
    {
        $start_date = isset($request['start_date'])? Carbon::parse($request['start_date'])->format('Y-m-d') :'';
        $end_date = isset($request['end_date'])? Carbon::parse($request['end_date'])->format('Y-m-d') :'';

        $total_users = User::selectRaw('DATE(created_at) AS `date`')
            ->selectRaw('MONTHNAME(created_at) AS `month`')
            ->selectRaw('COUNT(id) AS total_users')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(`user`.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })
            ->whereRaw("DATE(created_at) LIKE CONCAT_WS('', YEAR(NOW()), '-%')")
            ->groupByRaw('MONTH(created_at)')
            ->get()->toArray();

        $total_verified_users = User::selectRaw('DATE(created_at) AS `date`')
            ->selectRaw('MONTHNAME(created_at) AS `month`')
            ->selectRaw('COUNT(id) AS total_verified_users')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(`user`.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })->where('verified', 1)
            ->whereRaw("DATE(created_at) LIKE CONCAT_WS('', YEAR(NOW()), '-%')")
            ->groupByRaw('MONTH(created_at)')
            ->get()->toArray();

        $total_recruiter = Recruiter::selectRaw('DATE(created_at) AS `date`')
            ->selectRaw('MONTHNAME(created_at) AS `month`')
            ->selectRaw('count(id) As total_recruiter')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(recruiter.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })
            ->whereRaw("DATE(created_at) LIKE CONCAT_WS('', YEAR(NOW()), '-%')")
            ->groupByRaw('MONTH(created_at)')
            ->get()->toArray();

        $total_verified_recruiter = Recruiter::selectRaw('DATE(created_at) AS `date`')
            ->selectRaw('MONTHNAME(created_at) AS `month`')
            ->selectRaw('count(id) As total_verified_recruiter')
            ->when($start_date !== '', function ($where) use ($start_date, $end_date){
                $where->whereRaw('date(recruiter.created_at) BETWEEN "' . $start_date . '" AND "' . $end_date.'"');
            })->where('verified', 1)
            ->whereRaw("DATE(created_at) LIKE CONCAT_WS('', YEAR(NOW()), '-%')")
            ->groupByRaw('MONTH(created_at)')
            ->get()->toArray();

        if ($start_date !== '') {
            $userWhereRaw = 'AND date(`user`.created_at) BETWEEN "'.$start_date.'" AND "'.$end_date.'"';
            $recruiterWhereRaw = 'AND date(`recruiter`.created_at) BETWEEN "'.$start_date.'" AND "'.$end_date.'"';
        } else
            $userWhereRaw = $recruiterWhereRaw = '';

        $device_type_users = DB::select('SELECT month_number, `month`, device_type, COUNT(user_id) AS user_count FROM (
            SELECT user_session.*, MONTH(user_session.created_at) AS month_number,
        MONTHNAME(user_session.created_at) AS `month` FROM `user`
            JOIN user_session ON user_session.`user_id` = `user`.`id`
        WHERE DATE(user_session.created_at) LIKE CONCAT_WS("", YEAR(NOW()), "-%")
         '.$userWhereRaw.'
        GROUP BY user_session.`user_id` ORDER BY user_session.`id` DESC) asd
        GROUP BY `month`, device_type  ORDER BY month_number ASC;');

        $total_device_type_users=[];
        foreach ($device_type_users as $device_type_user) {
            $total_device_type_users[$device_type_user->month][] = [
                $device_type_user->device_type => $device_type_user->user_count
            ];
        }

        $device_type_recruiters = DB::select('SELECT month_number, `month`, device_type, COUNT(rec_id) AS recruiter_count FROM (
            SELECT recruiter_session.*, MONTH(recruiter_session.created_at) AS month_number,
        MONTHNAME(recruiter_session.created_at) AS `month` FROM `recruiter`
            JOIN recruiter_session ON recruiter_session.`rec_id` = `recruiter`.`id`
        WHERE DATE(recruiter_session.created_at) LIKE CONCAT_WS("", YEAR(NOW()), "-%")
        '.$recruiterWhereRaw.'
        GROUP BY recruiter_session.`rec_id` ORDER BY recruiter_session.`id` DESC) asd
        GROUP BY `month`, device_type  ORDER BY month_number ASC;');

        $total_device_type_recruiters=[];
        foreach ($device_type_recruiters as $device_type_recruiter) {
            $total_device_type_recruiters[$device_type_recruiter->month][] = [
                $device_type_recruiter->device_type => $device_type_recruiter->recruiter_count
            ];
        }

        return [
            'total_users'                  => $total_users,
            'total_verified_users'         => $total_verified_users,
            'total_recruiter'              => $total_recruiter,
            'total_verified_recruiter'     => $total_verified_recruiter,
            'total_device_type_users'      => $total_device_type_users,
            'total_device_type_recruiters' => $total_device_type_recruiters
        ];
    }
}
