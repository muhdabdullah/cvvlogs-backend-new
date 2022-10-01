<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recruiter extends Model
{
    use HasFactory;
    protected $table = 'recruiter';

    protected $visible = ['id', 'name', 'company'];

    protected $with = ['company'];

    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(RecruiterCompany::class, 'name', 'company_name');
    }
}
