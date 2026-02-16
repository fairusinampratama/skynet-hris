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
    protected static ?string $navigationGroup = 'Attendance Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TimePicker::make('check_in_time'),
                Forms\Components\TimePicker::make('check_out_time'),
                // Forms\Components\FileUpload::make('check_in_photo_path') ... REMOVED
                Forms\Components\Toggle::make('is_late'),
                Forms\Components\Toggle::make('is_flagged'),
                Forms\Components\Textarea::make('flag_reason'),
                Forms\Components\Textarea::make('work_summary'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->description(fn (Attendance $record): string => $record->user->employee->department->name ?? '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in_time')
                    ->time('H:i'),
                TextColumn::make('check_out_time')
                    ->time('H:i'),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->is_late ? 'late' : 'on_time';
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'late' => 'Late',
                        'on_time' => 'On Time',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'late' => 'danger',
                        'on_time' => 'success',
                        default => 'gray',
                    }),
                // ImageColumn::make('check_in_photo_path') ... REMOVED
                TextColumn::make('check_in_lat')
                    ->label('Location')
                    ->formatStateUsing(fn ($state) => $state ? 'View Map' : '-')
                    ->url(fn ($record) => $record->check_in_lat ? "https://www.google.com/maps/search/?api=1&query={$record->check_in_lat},{$record->check_in_long}" : null, true)
                    ->color('primary')
                    ->icon('heroicon-o-map-pin'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'on_time' => 'On Time',
                        'late' => 'Late',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'late') {
                            $query->where('is_late', true);
                        } elseif ($data['value'] === 'on_time') {
                            $query->where('is_late', false);
                        }
                    }),
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', now())),
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
