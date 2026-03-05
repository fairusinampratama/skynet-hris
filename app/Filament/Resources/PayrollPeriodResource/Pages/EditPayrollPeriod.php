<?php

namespace App\Filament\Resources\PayrollPeriodResource\Pages;

use App\Filament\Resources\PayrollPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollPeriod extends EditRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label(__('Generate Payroll'))
                ->icon('heroicon-o-calculator')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\PayrollPeriod $record) => !$record->isLocked())
                ->action(function (\App\Models\PayrollPeriod $record, \App\Services\PayrollService $service) {
                    try {
                        $service->generatePayroll($record);
                        \Filament\Notifications\Notification::make()
                            ->title(__('Payroll Generated Successfully'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('Error Generating Payroll'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('send_all_whatsapp')
                ->label(__('Send All via WA'))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\PayrollPeriod $record) => $record->payrolls()->exists())
                ->action(function (\App\Models\PayrollPeriod $record) {
                    $payrolls = $record->payrolls()->with('employee.user')->get();
                    $sentCount = 0;
                    $skippedCount = 0;

                    foreach ($payrolls as $payroll) {
                        if (!$payroll->employee->user->phone_number) {
                            $skippedCount++;
                            continue;
                        }

                        \App\Jobs\SendPayslipJob::dispatch($payroll);
                        $sentCount++;
                    }

                    if ($sentCount > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('Sending payslips via WhatsApp'))
                            ->body($skippedCount > 0 ? __("Skipped {$skippedCount} employees with no phone number") : '')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title(__('No payslips sent'))
                            ->body(__('No employees found with valid phone numbers.'))
                            ->warning()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
