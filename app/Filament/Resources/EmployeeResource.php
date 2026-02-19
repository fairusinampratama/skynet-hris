<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('Employee Management');
    }

    public static function getModelLabel(): string
    {
        return __('Employee');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Personal Information'))
                    ->schema([
                        // Unified User Fields
                        Forms\Components\TextInput::make('name')
                            ->label(__('Full Name'))
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email Address'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->confirmed(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        
                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make(__('Employment Details'))
                    ->schema([
                        Forms\Components\DatePicker::make('join_date')
                            ->required(),
                        Forms\Components\DatePicker::make('resignation_date')
                            ->label(__('Resignation Date'))
                            ->helperText('Leave empty if currently employed'),
                        // Role Type removed as per unified strategy
                        Forms\Components\TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Employee Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->searchable()
                    ->sortable(),
                // Role Type column removed
                Tables\Columns\IconColumn::make('face_descriptor')
                    ->label(__('Face Registered'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->face_descriptor))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('Status'))
                    ->placeholder(__('All Employees'))
                    ->trueLabel(__('Resigned'))
                    ->falseLabel(__('Active'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('resignation_date'),
                        false: fn (Builder $query) => $query->whereNull('resignation_date'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Resign Action
                Tables\Actions\Action::make('resign')
                    ->label(__('Resign'))
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn (Employee $record) => is_null($record->resignation_date))
                    ->form([
                        Forms\Components\DatePicker::make('resignation_date')
                            ->label(__('Resignation Date'))
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (Employee $record, array $data) {
                        $record->update(['resignation_date' => $data['resignation_date']]);
                        \Filament\Notifications\Notification::make()
                            ->title(__('Employee Resigned'))
                            ->success()
                            ->send();
                    }),

                // Rehire Action
                Tables\Actions\Action::make('rehire')
                    ->label(__('Rehire'))
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Employee $record) => !is_null($record->resignation_date))
                    ->requiresConfirmation()
                    ->modalHeading(__('Rehire Employee'))
                    ->modalDescription(__('Are you sure you want to rehire this employee? Their resignation date will be cleared.'))
                    ->action(function (Employee $record) {
                        $record->update(['resignation_date' => null]);
                        \Filament\Notifications\Notification::make()
                            ->title(__('Employee Rehired'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('resetFace')
                    ->label(__('Reset Face ID'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Employee $record) => !empty($record->face_descriptor)) // Hide if no face ID
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'face_descriptor' => null,
                            'profile_photo_path' => null 
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title(__('Face ID Reset Successfully'))
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Delete removed to prevent accidental data loss
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
