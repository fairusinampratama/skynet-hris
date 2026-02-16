<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_locked' => 'boolean', // Or use status enum
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'period_id');
    }
}
