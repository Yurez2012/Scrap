<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibitors extends Model
{
    protected $table = 'exhibitors';

    protected $fillable = [
        'exhibitor_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_fax',
        'company_logo',
        'company_facebook',
        'company_instagram',
        'company_linkedin',
        'company_youtube',
        'address1',
        'address2',
        'address3',
        'city',
        'postal',
        'country',
        'invoice_company_name',
        'invoice_email',
        'invoice_address1',
        'invoice_address2',
        'invoice_iso_code',
        'invoice_postal',
        'stand_id',
        'stand_nr',
        'stand_link',
        'project_id',
        'project_name',
        'project_name_en',
        'project_name_sv',
        'fair_catalog_text',
        'fair_catalogue_text_en',
        'fair_catalogue_text_sv',
        'meeting_reservation_link',
        'organisation_number',
        'url',
        'products',
        'themes',
    ];

    protected $casts = [
        'products' => 'array',
        'themes' => 'array',
    ];
}
