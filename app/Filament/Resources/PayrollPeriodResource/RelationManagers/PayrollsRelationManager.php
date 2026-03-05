<?php

namespace App\Filament\Resources\PayrollPeriodResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Ringkasan Gaji'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('Employee'))
                            ->relationship('employee.user', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('basic_salary')
                            ->label(__('Basic Salary'))
                            ->disabled(),
                        Forms\Components\TextInput::make('total_allowances')
                            ->label(__('Total Allowances'))
                            ->disabled(),
                        Forms\Components\TextInput::make('total_deductions')
                            ->label(__('Total Deductions'))
                            ->disabled(),
                        Forms\Components\TextInput::make('bonus')
                            ->label(__('Bonus'))
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $basic = (float) $get('basic_salary');
                                $allowances = (float) $get('total_allowances');
                                $deductions = (float) $get('total_deductions');
                                $bonus = (float) $state;
                                $set('net_salary', round($basic + $allowances + $bonus - $deductions, 0));
                            }),
                        Forms\Components\TextInput::make('net_salary')
                            ->label(__('Net Salary'))
                            ->disabled()
                            ->dehydrated(),
                    ]),
                
                Forms\Components\Section::make(__('Rincian Item (Dari Sistem)'))
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship(modifyQueryUsing: fn ($query) => $query->orderBy('id'))
                            ->label('')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Keterangan'))
                                    ->disabled()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('amount')
                                    ->label(__('Jumlah'))
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee.user.name')
            ->columns([
                TextColumn::make('employee.user.name')->label(__('Employee'))->searchable(),
                TextColumn::make('basic_salary')->label(__('Basic Salary'))->money('IDR')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_allowances')->label(__('Total Allowances'))->money('IDR')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_deductions')->label(__('Total Deductions'))->money('IDR')->sortable(),
                TextColumn::make('bonus')->label(__('Bonus'))->money('IDR')->sortable(),
                TextColumn::make('net_salary')->label(__('Net Salary'))->money('IDR')->sortable(),
                Tables\Columns\IconColumn::make('wa_sent_at')
                    ->label(__('Sent WA'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('items_count')->counts('items')->label(__('Items')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Generation is done on the Period layer, so no create here.
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('view_pdf')
                    ->label(__('View PDF'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (\App\Models\Payroll $record) => route('payroll.view', $record))
                    ->openUrlInNewTab(),
                Action::make('send_whatsapp')
                    ->label(__('Send to WA'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (\App\Models\Payroll $record) {
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
}
