<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BelongsToManyAlias;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne as HasOneAlias;

class Job extends Model
{
    use HasFactory;
    protected $table = 'job';

    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const StatusText = [
        self::PENDING  => 'Pending',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected'
    ];

    public static array $listingRule = [
        'admin_status' => 'in:' . self::APPROVED . ',' . self::REJECTED . ',' . self::PENDING
    ];

    public static array $ApproveStatusRule = [
        'id'     => 'required|array',
        'id.*'   => 'exists:job,id',
        'status' => 'required|in:' . self::APPROVED . ',' . self::REJECTED . ',' . self::PENDING
    ];

    protected $appends = ['job_admin_status', 'ago', 'job_id'];
    protected $with = ['country', 'state', 'city', 'recruiter', 'industry', 'workLevel', 'experience', 'functional_area', 'skill'];

    /**
     * @return string
     */
    public function getJobAdminStatusAttribute(): string
    {
        return self::StatusText[$this->is_admin_approved];
    }

    /**
     * @return int
     */
    public function getJobIdAttribute(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    /*public function getIsFavAttribute(): bool
    {
        $is_fav = false;
        if (auth()->guard('api')->user())
            $is_fav = $this->favJobs()->wherePivot('user_id', auth()->guard('api')->user()->id)->count() > 0;
        return $is_fav;
    }*/

    /**
     * @return string
     */
    public function getAgoAttribute(): string
    {
        return $this->created_at?$this->created_at->diffForHumans():'';
    }

    /**
     * @return HasOneAlias
     */
    public function country(): HasOneAlias
    {
        return $this->hasOne(Country::class,'id','country_id');
    }

    /**
     * @return HasOneAlias
     */
    public function state(): HasOneAlias
    {
        return $this->hasOne(State::class,'id','state_id');
    }

    /**
     * @return HasOneAlias
     */
    public function city(): HasOneAlias
    {
        return $this->hasOne(City::class,'id','city_id');
    }

    /**
     * @return HasOneAlias
     */
    public function recruiter(): HasOneAlias
    {
        return $this->hasOne(Recruiter::class,'id','recruiter_id');
    }

    /**
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class,'job_id','id');
    }

    /**
     * @return BelongsToManyAlias
     */
    public function industry(): BelongsToManyAlias
    {
        return $this->belongsToMany(Industries::class,'job_industry', 'job_id','industry_id', 'id');
    }

    /**
     * @return HasOneAlias
     */
    public function workLevel(): HasOneAlias
    {
        return $this->hasOne(WorkLevel::class,'id','work_level');
    }

    /**
     * @return BelongsToManyAlias
     */
    public function favJobs(): BelongsToManyAlias
    {
        return $this->belongsToMany(User::class, 'user_fav_jobs', 'job_id', 'user_id', 'id');
    }

    /**
     * @return HasOneAlias
     */
    public function experience(): HasOneAlias
    {
        return $this->hasOne(Experience::class,'id','experience_req');
    }

    /**
     * @return BelongsToManyAlias
     */
    public function functional_area()
    {
        return $this->belongsToMany(FunctionalArea::class,'job_functionalarea', 'job_id','func_id');
    }

    /**
     * @return BelongsToManyAlias
     */
    public function skill()
    {
        return $this->belongsToMany(Skill::class,'job_skills', 'job_id','skill_id');
    }
}
