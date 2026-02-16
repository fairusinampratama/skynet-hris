<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Schedule extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'is_off',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_off' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
