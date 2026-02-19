<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'office_name',
        'logo_path',
        'office_address',
        'office_lat',
        'office_long',
        'radius_meters',
        'transport_allowance',
        'meal_allowance',
        'late_fine_per_day',
    ];
}
