<?php

namespace App\Repositories;

use App\Models\ProfileUpdate;
use App\Models\UserVideo;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class SampleRepository
 * @package App\Data\Repositories
 */
class VideoRepository extends BaseRepository
{
    /**
     * @return string
     */
    function model(): string
    {
        return UserVideo::class;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getUserVideos($request): mixed
    {
        $status = $request['status'] ?? '';
        $recordPerPage = $request['per_page'] ?? 15;
        return ProfileUpdate::selectRaw('user_videos.id, user.id AS user_id, user.first_name, user.last_name, user.num, user_videos.link')
            ->selectRaw('IF(user_videos.status=0, "'.UserVideo::StatusText[UserVideo::PENDING].'", IF(user_videos.status='.UserVideo::PENDING.', "'.UserVideo::StatusText[UserVideo::PENDING].'", IF(user_videos.status='.UserVideo::APPROVED.', "'.UserVideo::StatusText[UserVideo::APPROVED].'", IF(user_videos.status='.UserVideo::REJECTED.', "'.UserVideo::StatusText[UserVideo::REJECTED].'", "")))) As status')
            ->join('user', 'user.id', 'profile_updates.user_id')
            ->join('user_videos', 'user_videos.user_id', 'profile_updates.user_id')
            ->when($status, function ($where) use ($status) {
                $where->where('user_videos.status', $status);
            })
            ->where([
                'profile_updates.headline' => 1,
                'profile_updates.proffessional' => 1,
                'profile_updates.profile' => 1,
                'profile_updates.qualification' => 1,
                'profile_updates.video' => 1,
                'user.verified' => 1,
            ])->orderBy('user_videos.id', 'desc')->paginate($recordPerPage);
    }

    /**
     * Update User's Uploaded Video Status To Approved Or Reject
     *
     * @param $request
     * @return bool
     */
    public function updateVideoStatus($request): bool
    {
        return $this->model->whereIn('id', $request['id'])->update(['status' => $request['status']]);
    }
}
