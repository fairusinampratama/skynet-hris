<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeRequestResource\Pages;
use App\Models\OvertimeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class OvertimeRequestResource extends Resource
{
    protected static ?string $model = OvertimeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Approvals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee.user', 'name')
                    ->disabled(),
                Forms\Components\DatePicker::make('date')->disabled(),
                Forms\Components\TextInput::make('hours')->disabled(),
                Forms\Components\Textarea::make('reason')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->visible(fn ($get) => $get('status') === 'rejected'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.user.name')->label('Employee')->searchable(),
                TextColumn::make('date')->date(),
                TextColumn::make('hours'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('created_at')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
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
            'index' => Pages\ListOvertimeRequests::route('/'),
            'create' => Pages\CreateOvertimeRequest::route('/create'),
            'edit' => Pages\EditOvertimeRequest::route('/{record}/edit'),
        ];
    }
}
