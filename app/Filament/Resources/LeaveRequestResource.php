<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): ?string
    {
        return __('Approvals');
    }

    public static function getModelLabel(): string
    {
        return __('Leave Request');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\Select::make('leave_type_id')
                    ->label(__('Leave Type'))
                    ->relationship('leaveType', 'name')
                    ->disabled(),
                Forms\Components\DatePicker::make('start_date')->label(__('Start Date'))->disabled(),
                Forms\Components\DatePicker::make('end_date')->label(__('End Date'))->disabled(),
                Forms\Components\Textarea::make('reason')->label(__('Reason'))->disabled(),
                Forms\Components\FileUpload::make('attachment_path')
                    ->label(__('Attachment'))
                    ->disk('public')
                    ->disabled()
                    ->downloadable(),
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
                TextColumn::make('user.name')->label(__('User'))->searchable(),
                TextColumn::make('leaveType.name')->label(__('Leave Type')),
                TextColumn::make('start_date')->label(__('Start Date'))->date(),
                TextColumn::make('end_date')->label(__('End Date'))->date(),
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
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
