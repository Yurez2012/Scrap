<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'headline',
        'summary',
        'job_title',
        'company_name',
        'location',
        'picture_url',
        'type_key_translation',
        'company_description',
        'company_website',
        'current_role',
        'hardwaresoftware_investing',
        'industry',
        'investment_region',
        'investor_type',
        'linkedin_profile',
        'quick_introduction_about_yourself',
        'typical_ticket_size',
        'what_are_you_looking_for',
        'what_is_your_investment_thesis',
        'will_you_join_the_investor_day',
        'investment_stage',
        'topics_of_interest',
        'attendee_type',
    ];
}
