<?php

namespace App\DTOs;

class SimulationResultData
{
    public function __construct(
        public readonly string $type,            // BUY, SELL, EXCHANGE
        public readonly ?int $sourceCryptoId,
        public readonly ?int $targetCryptoId,
        public readonly ?float $sourceAmount,
        public readonly ?float $targetAmount,
        public readonly ?float $sourcePriceUsd,
        public readonly ?float $targetPriceUsd,
        public readonly float $usdEquivalent,
        public readonly ?float $gtqEquivalent = null,
    ) {}

    /** Mapea el DTO a las columnas de la tabla simulations */
    public function toArray(): array
    {
        return [
            'type'             => $this->type,
            'source_crypto_id' => $this->sourceCryptoId,
            'target_crypto_id' => $this->targetCryptoId,
            'source_amount'    => $this->sourceAmount,
            'target_amount'    => $this->targetAmount,
            'source_price_usd' => $this->sourcePriceUsd,
            'target_price_usd' => $this->targetPriceUsd,
            'usd_equivalent'   => $this->usdEquivalent,
        ];
    }
}
