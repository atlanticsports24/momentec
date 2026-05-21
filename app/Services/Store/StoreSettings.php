<?php

namespace App\Services\Store;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

class StoreSettings
{
    private const CACHE_KEY = 'store_settings';

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return data_get($all, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        StoreSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::CACHE_KEY);
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return StoreSetting::query()
                ->pluck('value', 'key')
                ->map(fn ($v) => is_array($v) ? $v : json_decode($v ?? 'null', true))
                ->toArray();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
