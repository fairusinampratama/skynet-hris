<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Services\PayrollService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    
    public static function getNavigationGroup(): ?string
    {
        return __('Payroll');
    }

    public static function getModelLabel(): string
    {
        return __('Payroll');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payroll_period_id')
                    ->label(__('Payroll Period'))
                    ->relationship('period', 'id')
                    ->disabled(),
                Forms\Components\Select::make('employee_id')
                    ->label(__('Employee'))
                    ->relationship('employee.user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('basic_salary')->label(__('Basic Salary'))->disabled(),
                Forms\Components\TextInput::make('total_allowances')->label(__('Total Allowances'))->disabled(),
                Forms\Components\TextInput::make('total_deductions')->label(__('Total Deductions'))->disabled(),
                Forms\Components\TextInput::make('net_salary')->label(__('Net Salary'))->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period.month')
                     ->formatStateUsing(fn ($state) => \Carbon\Carbon::create(null, $state)->translatedFormat('F'))
                     ->label(__('Month')),
                TextColumn::make('employee.user.name')->label(__('Employee'))->searchable(),
                TextColumn::make('net_salary')->label(__('Net Salary'))->money('IDR'),
                Tables\Columns\IconColumn::make('wa_sent_at')
                    ->label(__('Sent WA'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('items_count')->counts('items')->label(__('Items')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->relationship('period', 'year'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('view_pdf')
                    ->label(__('View PDF'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Payroll $record) => route('payroll.view', $record))
                    ->openUrlInNewTab(),
                Action::make('send_whatsapp')
                    ->label(__('Send to WA'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Payroll $record) {
                        if (!$record->employee->user->phone_number) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Error: Employee has no phone number'))
                                ->danger()
                                ->send();
                            return;
                        }

                        \App\Jobs\SendPayslipJob::dispatch($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('Payslip sent to queue'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('send_bulk_whatsapp')
                        ->label(__('Send Selected via WA'))
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $sentCount = 0;
                            $skippedCount = 0;

                            foreach ($records as $payroll) {
                                if (!$payroll->employee->user->phone_number) {
                                    $skippedCount++;
                                    continue;
                                }

                                \App\Jobs\SendPayslipJob::dispatch($payroll);
                                $sentCount++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('Sending payslips via WhatsApp'))
                                ->body($skippedCount > 0 ? __("Skipped {$skippedCount} employees with no phone number") : '')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
        ];
    }
}
