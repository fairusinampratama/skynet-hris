<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { width: 100%; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info { text-align: right; }
        .company-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .company-address { font-size: 10px; color: #666; }
        .logo { max-width: 100px; max-height: 80px; float: left; }
        
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        
        .details-table { width: 100%; margin-bottom: 20px; }
        .details-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        
        .money-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .money-table th { background-color: #f4f4f4; padding: 8px; text-align: left; border: 1px solid #ddd; }
        .money-table td { padding: 8px; border: 1px solid #ddd; }
        .amount { text-align: right; font-family: monospace; }
        .deduction { color: #d9534f; }
        
        .total-row td { font-weight: bold; background-color: #f9f9f9; }
        .net-salary { font-size: 14px; background-color: #e8f5e9; }
        
        .footer { margin-top: 50px; width: 100%; }
        .signature { text-align: center; width: 200px; float: right; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; }
        .timestamp { font-size: 9px; color: #999; margin-top: 30px; text-align: center; clear: both; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($company && $company->logo_path)
                <!-- Assuming logo is stored in public disk, access via absolute path for DomPDF -->
                <img src="{{ storage_path('app/public/' . $company->logo_path) }}" class="logo" alt="Logo">
            @endif
            
            <div class="company-info">
                <div class="company-name">{{ $company->office_name ?? 'Company Name' }}</div>
                <div class="company-address">{{ $company->office_address ?? 'Office Address' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="title">Slip Gaji</div>

        <!-- Employee Details -->
        <table class="details-table">
            <tr>
                <td class="label">Nama Karyawan:</td>
                <td>{{ $payroll->employee->user->name }}</td>
                <td class="label">Periode:</td>
                <td>{{ $payroll->period->month }}/{{ $payroll->period->year }}</td>
            </tr>
            <tr>
                <td class="label">Departemen:</td>
                <td>{{ $payroll->employee->department->name ?? '-' }}</td>
                <td class="label">Dibuat Tanggal:</td>
                <td>{{ now()->format('d M Y') }}</td>
            </tr>
        </table>

        <!-- Earnings & Deductions -->
        <table class="money-table">
            <thead>
                <tr>
                    <th width="60%">Keterangan</th>
                    <th width="20%" style="text-align: right;">Penerimaan</th>
                    <th width="20%" style="text-align: right;">Potongan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Basic Salary -->
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="amount">{{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                    <td></td>
                </tr>

                <!-- Loop Items -->
                @php
                    $totalEarnings = $payroll->basic_salary;
                    $totalDeductions = 0;
                @endphp
                
                @foreach($payroll->items as $item)
                    <!-- Skip Basic Salary as it's already shown -->
                    @if($item->name === 'Gaji Pokok') @continue @endif
                    
                    <tr>
                        <td>{{ $item->name }}</td>
                        @if($item->type === 'earning')
                            @php $totalEarnings += $item->amount; @endphp
                            <td class="amount">{{ number_format($item->amount, 0, ',', '.') }}</td>
                            <td></td>
                        @else
                            @php $totalDeductions += $item->amount; @endphp
                            <td></td>
                            <td class="amount deduction">{{ number_format($item->amount, 0, ',', '.') }}</td>
                        @endif
                    </tr>
                @endforeach
                
                <!-- Totals -->
                <tr class="total-row">
                    <td>Total</td>
                    <td class="amount">{{ number_format($totalEarnings, 0, ',', '.') }}</td>
                    <td class="amount deduction">{{ number_format($totalDeductions, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Net Salary -->
                <tr class="total-row net-salary">
                    <td colspan="2" style="text-align: right; padding-right: 20px;">GAJI BERSIH (Diterima)</td>
                    <td class="amount">{{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer / Signature -->
        <div class="footer">
            <div class="signature">
                <p>Mengetahui,</p>
                <div class="signature-line"></div>
                <p>Manajer HRD</p>
            </div>
        </div>
        
        <div class="timestamp">
            Dokumen ini dibuat secara otomatis oleh komputer. Tanda tangan basah tidak diperlukan. | ID: {{ $payroll->id }}
        </div>
    </div>
</body>
</html>
