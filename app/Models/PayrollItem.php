<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
