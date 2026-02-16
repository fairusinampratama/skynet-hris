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

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payroll_period_id')
                    ->relationship('period', 'id')
                    ->disabled(),
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee.user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('basic_salary')->disabled(),
                Forms\Components\TextInput::make('total_allowances')->disabled(),
                Forms\Components\TextInput::make('total_deductions')->disabled(),
                Forms\Components\TextInput::make('net_salary')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period.month')
                     ->formatStateUsing(fn ($state) => date("F", mktime(0, 0, 0, $state, 10)))
                     ->label('Month'),
                TextColumn::make('employee.user.name')->searchable(),
                TextColumn::make('net_salary')->money('IDR'),
                TextColumn::make('items_count')->counts('items')->label('Items'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->relationship('period', 'year'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('generate_pdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-printer')
                    ->action(function (Payroll $record, PayrollService $service) {
                         $path = $service->generatePdf($record);
                         return response()->download(storage_path('app/public/' . $path));
                    }),
                Action::make('send_whatsapp')
                    ->label('Send to WA')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Payroll $record, PayrollService $service) {
                        if (!$record->pdf_path) {
                            $service->generatePdf($record);
                        }
                        
                        $url = asset('storage/' . $record->pdf_path);
                        $phone = $record->employee->user->phone_number;
                        
                        if (!$phone) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error: Employee has no phone number')
                                ->danger()
                                ->send();
                            return;
                        }

                        \App\Jobs\SendWhatsAppDocument::dispatch(
                            $phone, 
                            $url, 
                            "Sleep Gaji {$record->period->month}/{$record->period->year}"
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Payslip sent to queue')
                            ->success()
                            ->send();
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
            'index' => Pages\ListPayrolls::route('/'),
        ];
    }
}
