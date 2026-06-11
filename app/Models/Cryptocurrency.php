<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Cryptocurrency extends Model
{
    Protected $fillable = [
        'name',
        'symbol',
        'current_price',
        'market_cap',
        'total_volume',
    ];

    protected function casts(): array
    {
        return [
            'is_active'=> 'boolean',
        ];
    }
    
    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(PriceSnapshot::class);
    }

    public function sourceSimulations(): HasMany
    {
        return $this->hasMany(Simulation::class, 'source_crypto_id');
    }

    public function targetSimulations(): HasMany
    {
        return $this->hasMany(Simulation::class, 'target_crypto_id');
    }
}
