<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAsset extends Model
{
    protected $fillable = [
        'portfolio_id',
        'cryptocurrency_id',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
