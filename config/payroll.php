<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll Configuration
    |--------------------------------------------------------------------------
    |
    | Configure payroll calculation parameters including overtime rates,
    | deductions, allowances, and penalties.
    |
    */

    // Work hours calculation
    'work_hours_per_month' => env('PAYROLL_WORK_HOURS', 173), // Standard work hours per month
    'overtime_multiplier' => env('PAYROLL_OVERTIME_MULTIPLIER', 1.5), // OT pay multiplier

    // Fixed allowances (IDR)
    'allowances' => [
        'transport' => [
            'min' => env('PAYROLL_TRANSPORT_MIN', 300000),
            'max' => env('PAYROLL_TRANSPORT_MAX', 500000),
        ],
        'meal' => [
            'min' => env('PAYROLL_MEAL_MIN', 400000),
            'max' => env('PAYROLL_MEAL_MAX', 600000),
        ],
    ],

    // Deductions (as percentage of basic salary)
    'deductions' => [
        'bpjs_health' => env('PAYROLL_BPJS_HEALTH_RATE', 0.01), // 1%
        'bpjs_employment' => env('PAYROLL_BPJS_EMPLOYMENT_RATE', 0.02), // 2%
        'pph21' => env('PAYROLL_PPH21_RATE', 0.05), // 5% simplified
    ],

    // Penalties
    'late_fine_per_day' => env('PAYROLL_LATE_FINE', 50000), // IDR per late day

    // Departments eligible for higher overtime chance
    'high_overtime_departments' => ['Teknisi', 'NOC'],
    'high_overtime_chance' => 60, // percentage
    'normal_overtime_chance' => 30, // percentage

    // Overtime ranges (hours)
    'overtime' => [
        'min_hours' => 1,
        'max_hours' => 8,
    ],
];
