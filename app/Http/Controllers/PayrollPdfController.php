<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPdfController extends Controller
{
    public function view(Payroll $payroll, PayrollService $service)
    {
        // Ensure user is authorized to view this payroll
        // For now, we rely on middleware, but could added policy check here:
        // $this->authorize('view', $payroll);

        $pdfContent = $service->getPdfContent($payroll);
        $filename = "slip_gaji_{$payroll->period->month}_{$payroll->period->year}_{$payroll->employee->user->name}.pdf";

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    public function stream(Request $request, Payroll $payroll, PayrollService $service)
    {
        // Validates signature automatically via middleware in route definition
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired URL.');
        }

        $pdfContent = $service->getPdfContent($payroll);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="slip_gaji.pdf"');
    }
}
