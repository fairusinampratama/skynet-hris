<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'is_late' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updating(function ($attendance) {
            $period = PayrollPeriod::where('month', $attendance->date->month)
                ->where('year', $attendance->date->year)
                ->first();

            if ($period && $period->status === 'locked') {
                throw ValidationException::withMessages([
                    'error' => 'Cannot modify attendance for a locked payroll period.',
                ]);
            }
        });

        static::deleting(function ($attendance) {
             $period = PayrollPeriod::where('month', $attendance->date->month)
                ->where('year', $attendance->date->year)
                ->first();

            if ($period && $period->status === 'locked') {
                throw ValidationException::withMessages([
                    'error' => 'Cannot delete attendance for a locked payroll period.',
                ]);
            }
        });
    }
}
