<?php

namespace App\Jobs;

use App\Models\SecondaryFeedRow;
use App\Models\SyncRun;
use App\Services\Catalog\ProductCatalogSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;

class RunCatalogSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;

    public function __construct(
        public int $syncRunId,
        public string $mode = 'full'
    ) {}

    public function handle(ProductCatalogSyncService $catalog): void
    {
        $run = SyncRun::query()->find($this->syncRunId);
        if (! $run) {
            return;
        }

        $run->update([
            'status' => 'running',
            'started_at' => $run->started_at ?? now(),
            'current_step' => 'starting',
        ]);

        $primary = storage_path('app/'.$run->source_file);
        if (! File::exists($primary)) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_sample' => [['message' => 'Primary CSV not found at '.$primary]],
            ]);

            return;
        }

        $params = $run->parameters ?? [];
        $truncate = $params['truncate'] ?? [];

        try {
            if (($truncate['any'] ?? false) === true) {
                $catalog->truncate([
                    'images' => (bool) ($truncate['images'] ?? false),
                    'variant_display' => (bool) ($truncate['variant_display'] ?? false),
                    'category_product' => (bool) ($truncate['category_product'] ?? false),
                    'variants' => (bool) ($truncate['variants'] ?? false),
                    'products' => (bool) ($truncate['products'] ?? false),
                    'categories' => (bool) ($truncate['categories'] ?? false),
                    'brands' => (bool) ($truncate['brands'] ?? false),
                ]);
            }

            $mode = $this->mode;

            if ($mode === 'full' || $mode === 'categories') {
                $catalog->syncCategories($primary, $run);
            }
            if ($mode === 'full' || $mode === 'products') {
                $catalog->syncProducts($primary, $run);
            }
            if ($mode === 'full' || $mode === 'variants') {
                $catalog->syncVariants($primary, $run);
            }
            if ($mode === 'full' || $mode === 'aggregates') {
                $catalog->recalculateAggregates($run);
            }
            if ($mode === 'full' || $mode === 'images') {
                $catalog->enqueuePendingImages($run);
            }

            $secondaryRelative = $params['secondary_file'] ?? $run->secondary_source_file;
            if ($mode === 'full' && is_string($secondaryRelative) && $secondaryRelative !== '') {
                $this->importSecondaryFeed(storage_path('app/'.$secondaryRelative), $run);
            }

            $run->update([
                'status' => 'completed',
                'finished_at' => now(),
                'current_step' => 'done',
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_count' => $run->error_count + 1,
                'error_sample' => array_slice(array_merge($run->error_sample ?? [], [
                    ['message' => $e->getMessage()],
                ]), -20),
            ]);

            throw $e;
        }
    }

    protected function importSecondaryFeed(string $absolutePath, SyncRun $run): void
    {
        if (! File::exists($absolutePath)) {
            return;
        }

        $basename = basename($absolutePath);
        try {
            $reader = Reader::createFromPath($absolutePath, 'r');
            $reader->setHeaderOffset(0);
            $rowNum = 1;
            foreach ($reader->getRecords() as $record) {
                SecondaryFeedRow::query()->create([
                    'sync_run_id' => $run->id,
                    'source_filename' => $basename,
                    'row_number' => $rowNum++,
                    'payload' => $record,
                ]);
            }
        } catch (\Throwable) {
            $lines = file($absolutePath, FILE_IGNORE_NEW_LINES) ?: [];
            foreach ($lines as $i => $line) {
                SecondaryFeedRow::query()->create([
                    'sync_run_id' => $run->id,
                    'source_filename' => $basename,
                    'row_number' => $i + 1,
                    'payload' => ['raw' => $line],
                ]);
            }
        }
    }
}
