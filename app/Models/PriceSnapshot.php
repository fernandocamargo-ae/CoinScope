<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSnapshot extends Model
{
    protected $fillable = [
        'cryptocurrency_id',
        'price_usd',
        'price_gtq',
        'market_cap',
        'volume_24h',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'price_usd'   => 'decimal:8',
            'price_gtq'   => 'decimal:8',
            'market_cap'  => 'decimal:2',
            'volume_24h'  => 'decimal:2',
            'captured_at' => 'datetime',
        ];
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
