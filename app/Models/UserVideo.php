<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideo extends Model
{
    use HasFactory;

    protected $table = 'user_videos';

    const PENDING = 1;
    const APPROVED = 2;
    const REJECTED = 3;

    const StatusText = [
        self::PENDING  => 'Pending',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected'
    ];

    public static $updateStatusRule = [
        'id'     => 'required|array',
        'id.*'   => 'exists:user_videos,id',
        'status' => 'required|in:' . UserVideo::APPROVED . ',' . UserVideo::REJECTED . ',' . UserVideo::PENDING
    ];
}
