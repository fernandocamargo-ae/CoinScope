<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Simulation extends Model
{
    protected $fillable = [
        'user_id',
        'source_crypto_id',
        'target_crypto_id',
        'type',
        'source_amount',
        'target_amount',
        'source_price_usd',
        'target_price_usd',
        'usd_equivalent',
    ];

    protected function casts(): array
    {
        return [
            'source_amount'    => 'decimal:8',
            'target_amount'    => 'decimal:8',
            'source_price_usd' => 'decimal:8',
            'target_price_usd' => 'decimal:8',
            'usd_equivalent'   => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceCrypto(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class, 'source_crypto_id');
    }

    public function targetCrypto(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class, 'target_crypto_id');
    }
}
