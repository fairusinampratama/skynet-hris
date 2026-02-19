<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Holiday');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date')
                    ->label(__('Date'))
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->label(__('Year'))
                    ->numeric()
                    ->required()
                    ->default(now()->year),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label(__('Date'))->date()->sortable(),
                TextColumn::make('name')->label(__('Name'))->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'national_holiday' => __('National Holiday'),
                        'cuti_bersama' => __('Cuti Bersama'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'national_holiday' => 'danger',
                        'cuti_bersama' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('year')->label(__('Year'))->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(range(now()->year - 1, now()->year + 1))
                    ->default(now()->year),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import_holidays')
                    ->label(__('Import Holidays'))
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->form([
                        Forms\Components\TextInput::make('year')
                            ->label(__('Year to Import'))
                            ->numeric()
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Services\HolidayService $service) {
                        $year = $data['year'];
                        try {
                            $count = $service->fetchAndStoreHolidays($year);
                            
                            \Filament\Notifications\Notification::make()
                                ->title("Imported {$count} holidays for {$year}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Import Failed'))
                                ->body($e->getMessage())
                                ->danger()
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
