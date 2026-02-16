
<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .items { width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #ddd; padding: 8px; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Skynet HRIS</h1>
        <h2>Payslip for {{ $payroll->period->month }}/{{ $payroll->period->year }}</h2>
    </div>
    
    <div class="details">
        <p><strong>Employee:</strong> {{ $payroll->employee->user->name }}</p>
        <p><strong>Department:</strong> {{ $payroll->employee->department->name }}</p>
    </div>
    
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payroll->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ ucfirst($item->type) }}</td>
                <td style="text-align: right;">{{ number_format($item->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">Net Salary</td>
                <td style="text-align: right;">{{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
