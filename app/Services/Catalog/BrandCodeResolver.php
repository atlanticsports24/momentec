<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;

class BrandCodeResolver
{
    /** @var array<string, string>|null */
    protected ?array $map = null;

    /**
     * @return array<string, string> code => name
     */
    public function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $cacheKey = 'catalog.brand_code_map';
        $ttl = config('app.env') === 'local' ? 60 : 3600;

        $this->map = Cache::remember($cacheKey, $ttl, function (): array {
            $map = config('brand_codes.map', []);

            $fromSpec = $this->parseBrandMapFromSpecFile();
            if ($fromSpec !== []) {
                $map = array_merge($map, $fromSpec);
            }

            return $map;
        });

        return $this->map;
    }

    public function nameForCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }

        $map = $this->map();

        return $map[$code]
            ?? $map[ltrim($code, '0')]
            ?? $code;
    }

    public function forgetCache(): void
    {
        Cache::forget('catalog.brand_code_map');
        $this->map = null;
    }

    /**
     * Parse "10 = Augusta" lines from the Brand row in momentec_spec.xlsx.
     *
     * @return array<string, string>
     */
    protected function parseBrandMapFromSpecFile(): array
    {
        $relative = config('brand_codes.spec_path', 'imports/momentec_spec.xlsx');
        $path = storage_path('app/'.$relative);

        if (! File::isFile($path) || ! is_readable($path)) {
            return [];
        }

        if (File::exists($path) && str_ends_with(strtolower($path), '.xlsx')) {
            $lockFile = dirname($path).'/~$'.basename($path);
            if (File::exists($lockFile)) {
                return [];
            }
        }

        try {
            $reader = new Reader;
            $reader->open($path);

            $brandSpecText = null;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = [];
                    foreach ($row->getCells() as $cell) {
                        $cells[] = trim((string) $cell->getValue());
                    }

                    if (count($cells) < 3) {
                        continue;
                    }

                    $fieldName = $cells[1] ?? '';
                    if (strcasecmp($fieldName, 'Brand') === 0) {
                        $brandSpecText = $cells[3] ?? $cells[2] ?? null;
                        break 2;
                    }
                }
            }

            $reader->close();
        } catch (\Throwable) {
            return [];
        }

        if (! is_string($brandSpecText) || $brandSpecText === '') {
            return [];
        }

        return $this->parseBrandDefinitionText($brandSpecText);
    }

    /**
     * @return array<string, string>
     */
    public function parseBrandDefinitionText(string $text): array
    {
        $map = [];

        if (preg_match_all('/^\s*(\d+)\s*=\s*(.+?)\s*$/m', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $map[$match[1]] = trim($match[2]);
            }
        }

        return $map;
    }

    public function slugForCode(string $code): string
    {
        $name = $this->nameForCode($code);

        return Str::slug($name) ?: 'brand-'.preg_replace('/\D/', '', $code);
    }
}
