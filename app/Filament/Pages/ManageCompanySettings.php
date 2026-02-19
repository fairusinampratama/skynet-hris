<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;

class ManageCompanySettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Company Settings');
    }

    public function getTitle(): string
    {
        return __('Company Settings');
    }
    protected static string $view = 'filament.pages.manage-company-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = \App\Models\CompanySetting::firstOrNew();
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Office Location'))
                    ->description(__('Set the main office coordinates for employee attendance.'))
                    ->schema([
                        Forms\Components\TextInput::make('office_name')
                            ->label(__('Office Name'))
                            ->default(__('Main Office'))
                            ->required(),
                        Forms\Components\Textarea::make('office_address')
                            ->label(__('Office Address'))
                            ->rows(3),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('office_lat')
                                    ->label(__('Latitude'))
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('office_long')
                                    ->label(__('Longitude'))
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('radius_meters')
                                    ->label(__('Radius (meters)'))
                                    ->numeric()
                                    ->default(100)
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make(__('Payroll Settings'))
                    ->description(__('Configure fixed allowances and deductions used in payroll calculation.'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('transport_allowance')
                                    ->label(__('Transport Allowance (Rp)'))
                                    ->numeric()
                                    ->default(400000)
                                    ->required()
                                    ->helperText(__('Fixed monthly transport allowance per employee.')),
                                Forms\Components\TextInput::make('meal_allowance')
                                    ->label(__('Meal Allowance (Rp)'))
                                    ->numeric()
                                    ->default(500000)
                                    ->required()
                                    ->helperText(__('Fixed monthly meal allowance per employee.')),
                                Forms\Components\TextInput::make('late_fine_per_day')
                                    ->label(__('Late Fine per Day (Rp)'))
                                    ->numeric()
                                    ->default(50000)
                                    ->required()
                                    ->helperText(__('Deducted for each day employee arrives late.')),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $settings = \App\Models\CompanySetting::firstOrNew();
        $settings->fill($this->form->getState());
        $settings->save();

        \Filament\Notifications\Notification::make()
            ->title(__('Settings Saved'))
            ->success()
            ->send();
    }
    
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('Save Changes'))
                ->submit('create'),
        ];
    }


    public function getBreadcrumbs(): array
    {
        return [
            $this->getUrl() => $this->getTitle(),
        ];
    }
}
