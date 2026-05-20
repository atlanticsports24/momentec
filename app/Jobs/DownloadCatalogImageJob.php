<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadCatalogImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $imageId) {}

    public function handle(): void
    {
        $image = Image::query()->find($this->imageId);
        if (! $image || $image->download_status === Image::STATUS_COMPLETED) {
            return;
        }

        $url = $image->source_url;
        if (! is_string($url) || ! str_starts_with($url, 'http')) {
            $image->update([
                'download_status' => Image::STATUS_FAILED,
                'error_message' => 'Invalid source URL.',
            ]);

            return;
        }

        $duplicate = Image::query()
            ->whereNotNull('file_hash')
            ->where('source_url', $url)
            ->where('download_status', Image::STATUS_COMPLETED)
            ->where('id', '!=', $image->id)
            ->first();

        if ($duplicate && $duplicate->path) {
            $image->update([
                'path' => $duplicate->path,
                'disk' => $duplicate->disk,
                'file_hash' => $duplicate->file_hash,
                'download_status' => Image::STATUS_COMPLETED,
                'error_message' => null,
            ]);

            return;
        }

        try {
            $response = Http::timeout(120)
                ->retry(3, 500)
                ->withHeaders(['User-Agent' => 'MomentecCatalogSync/1.0'])
                ->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }

            $body = $response->body();
            $hash = hash('sha256', $body);
            $ext = $this->guessExtension($url, $response->header('Content-Type'));

            $relativePath = 'products/'.$hash.$ext;
            Storage::disk('public')->put($relativePath, $body);

            $image->update([
                'path' => $relativePath,
                'disk' => 'public',
                'file_hash' => $hash,
                'download_status' => Image::STATUS_COMPLETED,
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $image->update([
                'download_status' => Image::STATUS_FAILED,
                'error_message' => Str::limit($e->getMessage(), 2000),
            ]);
        }
    }

    protected function guessExtension(string $url, ?string $contentType): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $fromUrl = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($fromUrl, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return '.'.$fromUrl;
        }

        return match (true) {
            str_contains((string) $contentType, 'png') => '.png',
            str_contains((string) $contentType, 'gif') => '.gif',
            str_contains((string) $contentType, 'webp') => '.webp',
            default => '.jpg',
        };
    }
}
