<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Admin\UpdateUserVideoRequest;
use App\Repositories\VideoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoApiController extends BaseApiController
{
    /**
     * @var VideoRepository
     */
    protected VideoRepository $videoRepo;

    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepo = $videoRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserVideos(Request $request): JsonResponse
    {
        $data = $this->videoRepo->getUserVideos($request->only(['per_page', 'status']));
        return $this->sendResponse($data, __('response.success'));
    }

    /**
     * @param UpdateUserVideoRequest $request
     * @return JsonResponse
     */
    public function updateVideoStatus(UpdateUserVideoRequest $request): JsonResponse
    {
        $data = $this->videoRepo->updateVideoStatus($request->only(['id', 'status']));
        return $this->sendResponse($data, __('response.update'));
    }
}
