<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne as HasOneAlias;

class Application extends Model
{
    use HasFactory;

    protected $table = 'job_application';

    protected $fillable = [
        'job_id',
        'user_id',
        'come_from',
    ];
}
