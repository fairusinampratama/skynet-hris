<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'period_id');
    }

    /**
     * Check if period is locked
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Check if period is finalized or locked
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['finalized', 'locked']);
    }

    /**
     * Get formatted period name
     */
    public function getFormattedPeriodAttribute(): string
    {
        return date("F", mktime(0, 0, 0, $this->month, 10)) . " {$this->year}";
    }

    /**
     * Scope for draft periods
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for editable periods (draft or finalized, not locked)
     */
    public function scopeEditable($query)
    {
        return $query->whereIn('status', ['draft', 'finalized']);
    }
}
