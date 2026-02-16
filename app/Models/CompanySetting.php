<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'office_name',
        'office_address',
        'office_lat',
        'office_long',
        'radius_meters',
    ];
}
