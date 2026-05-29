<?php

namespace App\Filament\Pages;

use App\Models\Country;
use App\Models\Currency;
use App\Models\OrderStatus;
use App\Models\Zone;
use App\Services\Store\StoreSettings;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationLabel = 'Store Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(StoreSettings::class);

        $this->form->fill([
            'store_name' => $settings->get('store_name', 'Momentec'),
            'store_email' => $settings->get('store_email'),
            'default_country_id' => $settings->get('default_country_id'),
            'default_zone_id' => $settings->get('default_zone_id'),
            'default_currency_id' => $settings->get('default_currency_id'),
            'default_order_status_id' => $settings->get('default_order_status_id'),
            'tax_enabled' => (bool) $settings->get('tax_enabled', false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')->required(),
                        Forms\Components\TextInput::make('store_email')->email(),
                    ]),
                Forms\Components\Section::make('Defaults')
                    ->schema([
                        Forms\Components\Select::make('default_country_id')
                            ->label('Default country')
                            ->options(fn () => Country::query()->where('is_enabled', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('default_zone_id', null)),
                        Forms\Components\Select::make('default_zone_id')
                            ->label('Default zone')
                            ->options(function (Forms\Get $get) {
                                $countryId = $get('default_country_id');
                                if (! $countryId) {
                                    return [];
                                }

                                return Zone::query()
                                    ->where('country_id', $countryId)
                                    ->where('is_enabled', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                        Forms\Components\Select::make('default_currency_id')
                            ->label('Default currency')
                            ->options(fn () => Currency::query()->where('is_enabled', true)->orderBy('title')->pluck('title', 'id'))
                            ->required(),
                        Forms\Components\Select::make('default_order_status_id')
                            ->label('Default order status (new orders)')
                            ->options(fn () => OrderStatus::query()->orderBy('sort_order')->pluck('name', 'id'))
                            ->required()
                            ->helperText('Status assigned when a customer places an order (e.g. Missing, Pending).'),
                    ]),
                Forms\Components\Section::make('Tax')
                    ->description('When enabled, checkout calculates tax from the zone tax rate set under Localisation → Zones.')
                    ->schema([
                        Forms\Components\Toggle::make('tax_enabled')
                            ->label('Enable tax')
                            ->helperText('Turn off to charge no tax at checkout regardless of zone rates.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        app(StoreSettings::class)->setMany($data);

        Notification::make()
            ->title('Store settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Save')
                ->submit('save'),
        ];
    }
}
