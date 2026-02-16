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
        'basic_salary',
        'role_type',
        'profile_photo_path',
        'face_descriptor',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

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
