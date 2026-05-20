<?php

namespace App\Filament\Support;

use Filament\Forms;
use Illuminate\Support\HtmlString;

class CatalogImageForm
{
    public static function productMainImageSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Main image')
            ->description('Shows the downloaded image from sync, or upload a replacement (stored on this server).')
            ->schema([
                Forms\Components\Placeholder::make('main_image_preview')
                    ->label('Current image')
                    ->content(function ($record): HtmlString|string {
                        if (! $record) {
                            return '—';
                        }

                        $url = $record->mainImageUrl();
                        if (! $url) {
                            return new HtmlString('<span class="text-sm text-gray-500">No image yet. Upload below or run image sync.</span>');
                        }

                        return new HtmlString(
                            '<div class="inline-block max-w-[10rem]">'
                            .'<img src="'.e($url).'" alt="" class="h-24 w-24 rounded-lg border border-gray-200 object-contain dark:border-gray-700" />'
                            .'</div>'
                        );
                    })
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('main_image_upload')
                    ->label('Upload new image (replaces current after Save)')
                    ->disk('public')
                    ->directory('products/manual')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('100')
                    ->maxFiles(1)
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->helperText('Choose a file with Browse, then click Save at the top of the page.'),
            ])
            ->columns(1);
    }

    public static function variantImagesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Images')
            ->description('Downloaded images from the feed appear here. Upload to replace stored files.')
            ->schema([
                self::variantImageField('main', 'Main image'),
                self::variantImageField('swatch', 'Swatch image'),
                self::variantImageField('other', 'Other / gallery image'),
                self::variantImageField('size_chart', 'Size chart image'),
            ])
            ->columns(2);
    }

    protected static function variantImageField(string $role, string $label): Forms\Components\Group
    {
        $uploadField = "variant_image_upload_{$role}";
        $previewField = "variant_image_preview_{$role}";

        return Forms\Components\Group::make([
            Forms\Components\Placeholder::make($previewField)
                ->label($label)
                ->content(function ($record) use ($role): HtmlString|string {
                    if (! $record) {
                        return '—';
                    }

                    $url = $record->imageUrl($role);
                    if (! $url) {
                        return new HtmlString('<span class="text-xs text-gray-500">None</span>');
                    }

                    return new HtmlString(
                        '<div class="inline-block max-w-[8rem]">'
                        .'<img src="'.e($url).'" alt="" class="h-20 w-20 rounded border border-gray-200 object-contain dark:border-gray-700" />'
                        .'</div>'
                    );
                }),
            Forms\Components\FileUpload::make($uploadField)
                ->label("Replace {$label}")
                ->disk('public')
                ->directory('products/variants')
                ->visibility('public')
                ->image()
                ->imagePreviewHeight('80')
                ->maxFiles(1)
                ->maxSize(10240)
                ->dehydrated(false),
        ])->columnSpan(1);
    }
}
