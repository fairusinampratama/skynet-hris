<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    public static function getNavigationGroup(): ?string
    {
        return __('Attendance Management');
    }

    public static function getModelLabel(): string
    {
        return __('Attendance');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('Employee'))
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label(__('Date'))
                    ->required(),
                Forms\Components\TimePicker::make('check_in_time')->label(__('Check In Time')),
                Forms\Components\TimePicker::make('check_out_time')->label(__('Check Out Time')),
                Forms\Components\Toggle::make('is_late')->label(__('Late')),
                Forms\Components\Toggle::make('is_flagged')->label(__('Flagged')),
                Forms\Components\Textarea::make('flag_reason')->label(__('Flag Reason')),
                Forms\Components\Textarea::make('work_summary')->label(__('Work Summary')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Employee'))
                    ->description(fn (Attendance $record): string => $record->user->employee->department->name ?? '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in_time')
                    ->time('H:i')
                    ->label(__('Check In Time'))
                    ->color(fn (Attendance $record) => $record->is_late ? 'danger' : 'gray')
                    ->description(fn (Attendance $record) => $record->is_late ? __('Late Arrival') : null),
                TextColumn::make('check_out_time')
                    ->label(__('Check Out Time'))
                    ->time('H:i'),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->is_late ? 'late' : 'on_time';
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'late' => __('Late'),
                        'on_time' => __('On Time'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'late' => 'danger',
                        'on_time' => 'success',
                        default => 'gray',
                    }),
                // ImageColumn::make('check_in_photo_path') ... REMOVED
                TextColumn::make('check_in_lat')
                    ->label(__('Location'))
                    ->formatStateUsing(fn ($state) => $state ? __('View Map') : '-')
                    ->url(fn ($record) => $record->check_in_lat ? "https://www.google.com/maps/search/?api=1&query={$record->check_in_lat},{$record->check_in_long}" : null, true)
                    ->color('primary')
                    ->icon('heroicon-o-map-pin'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'on_time' => __('On Time'),
                        'late' => __('Late'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'late') {
                            $query->where('is_late', true);
                        } elseif ($data['value'] === 'on_time') {
                            $query->where('is_late', false);
                        }
                    }),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
