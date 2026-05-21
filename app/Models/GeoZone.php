<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GeoZone extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'geo_zone_zone')
            ->withPivot('country_id')
            ->withTimestamps();
    }
}
