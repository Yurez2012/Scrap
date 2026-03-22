<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HannoverProfile extends Model
{
    protected $table = 'hannover_profiles';

    protected $fillable = [
        'profile_id',
        'first_name',
        'last_name',
        'job_title',
        'company_name',
        'bio',
        'photo_url',
        'email',
        'phone',
        'linkedin_url',
        'website',
        'raw_data',
        'data_fetched',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'data_fetched' => 'boolean',
    ];
}
