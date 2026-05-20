<?php

namespace App\Filament\Pages;

use App\Jobs\RunCatalogSyncJob;
use App\Models\SyncRun;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class SyncCenter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Sync';

    protected static ?string $navigationLabel = 'Sync Center';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.sync-center';

    public ?array $data = [];

    public ?int $activeSyncRunId = null;

    public function mount(): void
    {
        $imports = $this->importFileOptions();
        $this->form->fill([
            'primary_file' => array_key_first($imports) ?: null,
            'secondary_file' => null,
            'truncate_brands' => false,
            'truncate_categories' => false,
            'truncate_products' => false,
            'truncate_variants' => false,
            'truncate_category_product' => false,
            'truncate_variant_display' => false,
            'truncate_images' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('primary_file')
                    ->label('Primary product CSV')
                    ->options(fn () => $this->importFileOptions())
                    ->required()
                    ->native(false),
                \Filament\Forms\Components\Select::make('secondary_file')
                    ->label('Secondary CSV (optional)')
                    ->options(fn () => $this->importFileOptions())
                    ->nullable()
                    ->native(false),
                \Filament\Forms\Components\Section::make('Remove existing data before sync')
                    ->description('Check only the tables you want cleared before this import runs. Unchecked data is left as-is.')
                    ->schema([
                        \Filament\Forms\Components\Checkbox::make('truncate_brands')
                            ->label('Brands'),
                        \Filament\Forms\Components\Checkbox::make('truncate_categories')
                            ->label('Categories'),
                        \Filament\Forms\Components\Checkbox::make('truncate_products')
                            ->label('Products'),
                        \Filament\Forms\Components\Checkbox::make('truncate_variants')
                            ->label('Variants'),
                        \Filament\Forms\Components\Checkbox::make('truncate_category_product')
                            ->label('Category ↔ product links'),
                        \Filament\Forms\Components\Checkbox::make('truncate_variant_display')
                            ->label('Variant display settings'),
                        \Filament\Forms\Components\Checkbox::make('truncate_images')
                            ->label('Images metadata'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_full')
                ->label('Sync all')
                ->color('primary')
                ->action(fn () => $this->dispatchSync('full')),
            Action::make('sync_categories')
                ->label('Categories only')
                ->action(fn () => $this->dispatchSync('categories')),
            Action::make('sync_products')
                ->label('Products only')
                ->action(fn () => $this->dispatchSync('products')),
            Action::make('sync_variants')
                ->label('Variants only')
                ->action(fn () => $this->dispatchSync('variants')),
            Action::make('sync_aggregates')
                ->label('Aggregates only')
                ->action(fn () => $this->dispatchSync('aggregates')),
            Action::make('sync_images')
                ->label('Image jobs only')
                ->action(fn () => $this->dispatchSync('images')),
        ];
    }

    public function getActiveRunProperty(): ?SyncRun
    {
        return $this->activeSyncRunId
            ? SyncRun::query()->find($this->activeSyncRunId)
            : null;
    }

    protected function dispatchSync(string $mode): void
    {
        $data = $this->form->getState();
        $primary = $data['primary_file'] ?? null;
        if (! is_string($primary) || $primary === '') {
            Notification::make()->title('Choose a primary CSV file.')->danger()->send();

            return;
        }

        $truncate = $this->resolvedTruncateFlags($data);

        $secondary = $data['secondary_file'] ?? null;
        if ($secondary === '') {
            $secondary = null;
        }

        $run = SyncRun::query()->create([
            'user_id' => auth()->id(),
            'mode' => $mode,
            'status' => 'queued',
            'source_file' => 'imports/'.$primary,
            'secondary_source_file' => $secondary ? 'imports/'.$secondary : null,
            'parameters' => [
                'truncate' => $truncate,
                'secondary_file' => $secondary ? 'imports/'.$secondary : null,
            ],
            'started_at' => now(),
        ]);

        RunCatalogSyncJob::dispatch($run->id, $mode);

        $this->activeSyncRunId = $run->id;

        $run->refresh();

        if ($run->status === 'completed') {
            Notification::make()
                ->title('Sync completed')
                ->body('The import finished in this request (queue driver is `sync`).')
                ->success()
                ->send();
        } elseif ($run->status === 'failed') {
            Notification::make()
                ->title('Sync failed')
                ->body('Check the Sync Center run details or `error_sample` on the sync run.')
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Sync queued')
                ->body('Run `php artisan queue:work database` in a terminal to process jobs (or set `QUEUE_CONNECTION=sync` in `.env` for local).')
                ->success()
                ->send();
        }
    }

    /**
     * @return array<string, string>
     */
    protected function importFileOptions(): array
    {
        $dir = storage_path('app/imports');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $out = [];
        foreach (File::files($dir) as $file) {
            if (strtolower($file->getExtension()) === 'csv') {
                $out[$file->getFilename()] = $file->getFilename();
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function resolvedTruncateFlags(array $data): array
    {
        $any =
            ($data['truncate_brands'] ?? false)
            || ($data['truncate_categories'] ?? false)
            || ($data['truncate_products'] ?? false)
            || ($data['truncate_variants'] ?? false)
            || ($data['truncate_category_product'] ?? false)
            || ($data['truncate_variant_display'] ?? false)
            || ($data['truncate_images'] ?? false);

        return [
            'any' => $any,
            'brands' => (bool) ($data['truncate_brands'] ?? false),
            'categories' => (bool) ($data['truncate_categories'] ?? false),
            'products' => (bool) ($data['truncate_products'] ?? false),
            'variants' => (bool) ($data['truncate_variants'] ?? false),
            'category_product' => (bool) ($data['truncate_category_product'] ?? false),
            'variant_display' => (bool) ($data['truncate_variant_display'] ?? false),
            'images' => (bool) ($data['truncate_images'] ?? false),
        ];
    }
}
