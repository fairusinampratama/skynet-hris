<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        // Prevent updates/deletes on EXISTING payrolls when period is locked
        // Note: This allows initial creation during seeding but prevents modifications later
        static::updating(function ($payroll) {
            // Only check if this is an existing record being updated
            if ($payroll->exists && $payroll->period && $payroll->period->isLocked()) {
                // Allow updates if only pdf_path is changing (and auto-updated timestamps)
                $dirty = $payroll->getDirty();
                unset($dirty['updated_at']);
                
                // If the only remaining dirty attribute is pdf_path, allow it
                if (count($dirty) === 1 && isset($dirty['pdf_path'])) {
                    return;
                }

                // If only wa_sent_at is changing, allow it
                if (count($dirty) === 1 && isset($dirty['wa_sent_at'])) {
                    return;
                }
                
                // Allow both pdf_path and wa_sent_at to change
                if (count($dirty) <= 2 && !array_diff(array_keys($dirty), ['pdf_path', 'wa_sent_at'])) {
                    return;
                }
                
                throw new \RuntimeException('Cannot update payroll for locked period');
            }
        });

        static::deleting(function ($payroll) {
            if ($payroll->period && $payroll->period->isLocked()) {
                throw new \RuntimeException('Cannot delete payroll for locked period');
            }
        });
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Get gross salary (before deductions)
     */
    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances;
    }

    /**
     * Get formatted net salary in IDR
     */
    public function getFormattedNetSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->net_salary, 0, ',', '.');
    }
}
