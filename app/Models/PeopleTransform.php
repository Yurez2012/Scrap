<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeopleTransform extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'person_uuid',
        'user_uuid',
        'address',
        'biography',
        'email',
        'firstName',
        'lastName',
        'jobTitle',
        'mobilePhone',
        'organization',
        'socialNetworks',
        'websiteUrl',
    ];
}
