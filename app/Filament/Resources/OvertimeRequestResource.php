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

    public static function getNavigationGroup(): ?string
    {
        return __('Approvals');
    }

    public static function getModelLabel(): string
    {
        return __('Overtime Request');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label(__('Employee'))
                    ->relationship('employee.user', 'name')
                    ->disabled(),
                Forms\Components\DatePicker::make('date')->label(__('Date'))->disabled(),
                Forms\Components\TextInput::make('hours')->label(__('Hours'))->disabled(),
                Forms\Components\Textarea::make('reason')->label(__('Reason'))->disabled(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending'  => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label(__('Rejection Reason'))
                    ->visible(fn ($get) => $get('status') === 'rejected'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.user.name')->label(__('Employee'))->searchable(),
                TextColumn::make('date')->label(__('Date'))->date(),
                TextColumn::make('hours')->label(__('Hours')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('created_at')->label(__('Created at'))->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
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
