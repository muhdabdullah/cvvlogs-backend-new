<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne as HasOneAlias;

class RecruiterCompany extends Model
{
    use HasFactory;
    protected $table = 'recruiter_company';

    protected $visible = ['id', 'name', 'industry'];

    protected $with = ['industry'];

    /**
     * @return HasOneAlias
     */
    public function industry(): HasOneAlias
    {
        return $this->hasOne(Industries::class,'id','category_id');
    }
}
