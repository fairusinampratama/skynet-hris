<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;

class ManageCompanySettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Company Settings';
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
                Forms\Components\Section::make('Office Location')
                    ->description('Set the main office coordinates for employee attendance.')
                    ->schema([
                        Forms\Components\TextInput::make('office_name')
                            ->default('Main Office')
                            ->required(),
                        Forms\Components\Textarea::make('office_address')
                            ->rows(3),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('office_lat')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('office_long')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('radius_meters')
                                    ->label('Radius (meters)')
                                    ->numeric()
                                    ->default(100)
                                    ->required(),
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
            ->title('Settings Saved')
            ->success()
            ->send();
    }
    
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('create'),
        ];
    }
}
