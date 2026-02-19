<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'join_date',
        'resignation_date',
        'basic_salary',
        'role_type',
        'profile_photo_path',
        'face_descriptor',
    ];

    protected $casts = [
        'join_date' => 'date',
        'resignation_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    /**
     * Check if employee is active during a specific period (month/year)
     */
    public function isActiveDuring($month, $year): bool
    {
        $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // 1. Must have joined ON or BEFORE the end of the period
        // (Joined Jan 31 -> Active in Jan)
        if ($this->join_date > $periodEnd) {
            return false;
        }

        // 2. Must NOT have resigned BEFORE the start of the period
        // (Resigned Dec 31 -> Inactive in Jan)
        if ($this->resignation_date && $this->resignation_date < $periodStart) {
            return false;
        }

        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }
}
