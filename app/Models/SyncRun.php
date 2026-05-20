<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    protected $fillable = [
        'user_id',
        'mode',
        'status',
        'source_file',
        'secondary_source_file',
        'parameters',
        'current_step',
        'total_rows',
        'processed_rows',
        'error_count',
        'error_sample',
        'log_path',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'error_sample' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'error_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressPercent(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(min(100, ($this->processed_rows / $this->total_rows) * 100), 2);
    }
}
