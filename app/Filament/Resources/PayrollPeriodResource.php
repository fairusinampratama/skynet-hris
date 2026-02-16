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
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('month')
                    ->options([
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'finalized' => 'Finalized',
                        'locked' => 'Locked',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')->formatStateUsing(fn ($state) => date("F", mktime(0, 0, 0, $state, 10))),
                TextColumn::make('year'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'finalized' => 'info',
                        'locked' => 'success',
                    }),
                TextColumn::make('payrolls_count')->counts('payrolls')->label('Employees'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('generate')
                    ->label('Generate Payroll')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function (PayrollPeriod $record, PayrollService $service) {
                        $service->generatePayroll($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Payroll Generated Successfully')
                            ->success()
                            ->send();
                    }),
                 Action::make('download_all')
                    ->label('Download All PDFs')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (PayrollPeriod $record) => $record->status !== 'draft')
                    ->action(function (PayrollPeriod $record) {
                        // Logic to zip all PDFs could go here
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
