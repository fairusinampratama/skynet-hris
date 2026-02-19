<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeShiftResource\Pages;
use App\Models\EmployeeShift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class EmployeeShiftResource extends Resource
{
    protected static ?string $model = EmployeeShift::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee.user', 'name')
                    ->label(__('Employee'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('day_of_week')
                    ->options([
                        0 => __('Sunday'),
                        1 => __('Monday'),
                        2 => __('Tuesday'),
                        3 => __('Wednesday'),
                        4 => __('Thursday'),
                        5 => __('Friday'),
                        6 => __('Saturday'),
                    ])
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.user.name')
                    ->label(__('Employee'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => __('Sunday'),
                        1 => __('Monday'),
                        2 => __('Tuesday'),
                        3 => __('Wednesday'),
                        4 => __('Thursday'),
                        5 => __('Friday'),
                        6 => __('Saturday'),
                        default => __('Unknown'),
                    })
                    ->sortable(),
                TextColumn::make('start_time')->time(),
                TextColumn::make('end_time')->time(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee.user', 'name'),
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->options([
                        0 => __('Sunday'),
                        1 => __('Monday'),
                        2 => __('Tuesday'),
                        3 => __('Wednesday'),
                        4 => __('Thursday'),
                        5 => __('Friday'),
                        6 => __('Saturday'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListEmployeeShifts::route('/'),
            'create' => Pages\CreateEmployeeShift::route('/create'),
            'edit' => Pages\EditEmployeeShift::route('/{record}/edit'),
        ];
    }
}
