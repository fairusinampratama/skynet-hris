<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollPeriodResource\Pages;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationGroup(): ?string
    {
        return __('Payroll');
    }

    public static function getModelLabel(): string
    {
        return __('Payroll Period');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('month')
                    ->label(__('Month'))
                    ->options([
                        1 => __('January'), 2 => __('February'), 3 => __('March'), 4 => __('April'),
                        5 => __('May'), 6 => __('June'), 7 => __('July'), 8 => __('August'),
                        9 => __('September'), 10 => __('October'), 11 => __('November'), 12 => __('December'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->label(__('Year'))
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'      => __('Draft'),
                        'finalized'  => __('Finalized'),
                        'locked'     => __('Locked'),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->label(__('Month'))
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::create(null, $state)->translatedFormat('F')),
                TextColumn::make('year')->label(__('Year')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'finalized' => 'info',
                        'locked' => 'success',
                    }),
                TextColumn::make('payrolls_count')->counts('payrolls')->label(__('Employees')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('generate')
                    ->label(__('Generate Payroll'))
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPeriod $record) => !$record->isLocked())
                    ->action(function (PayrollPeriod $record, PayrollService $service) {
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
                Action::make('download_all')
                    ->label(__('Download All PDFs'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (PayrollPeriod $record) => $record->status !== 'draft')
                    ->action(function (PayrollPeriod $record) {
                        // Logic to zip all PDFs could go here
                    }),
                Action::make('send_all_whatsapp')
                    ->label(__('Send All via WA'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPeriod $record) => $record->payrolls()->exists())
                    ->action(function (PayrollPeriod $record) {
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollPeriods::route('/'),
            'create' => Pages\CreatePayrollPeriod::route('/create'),
            'edit' => Pages\EditPayrollPeriod::route('/{record}/edit'),
        ];
    }
}
