<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecondaryFeedRow extends Model
{
    protected $fillable = [
        'sync_run_id',
        'source_filename',
        'row_number',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'row_number' => 'integer',
        ];
    }

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(SyncRun::class);
    }
}
