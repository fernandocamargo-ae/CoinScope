<?php

namespace App\Services;

use App\DTOs\SimulationResultData;
use App\Models\Cryptocurrency;
use App\Models\Simulation;
use App\Models\User;
use App\Repositories\Contracts\SimulationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SimulationService
{
    public function __construct(
        private readonly CryptoPriceService $prices,
        private readonly SimulationRepositoryInterface $simulations,
    ) {}

    /**
     * Calcula una compra SIN guardar nada (preview). RN-02: solo se guarda si el usuario decide.
     */
    public function previewBuy(Cryptocurrency $crypto, float $usdAmount): SimulationResultData
    {
        $price    = $this->prices->getCurrentPrice($crypto);
        $quantity = $price > 0 ? $usdAmount / $price : 0;

        return new SimulationResultData(
            type:           'BUY',
            sourceCryptoId: null,
            targetCryptoId: $crypto->id,
            sourceAmount:   null,
            targetAmount:   $quantity,
            sourcePriceUsd: null,
            targetPriceUsd: $price,
            usdEquivalent:  $usdAmount,
        );
    }

    /**
     * Ejecuta y GUARDA la compra: crea la simulación y actualiza el portafolio.
     * Todo dentro de una transacción para que no quede a medias (integridad financiera).
     */
    public function executeBuy(User $user, Cryptocurrency $crypto, float $usdAmount): Simulation
    {
        $portfolio = $user->portfolio;

        if ($usdAmount <= 0) {
            throw new \DomainException('El monto debe ser mayor a cero.');
        }
        if ((float) $portfolio->usd_balance < $usdAmount) {
            throw new \DomainException('Saldo USD insuficiente para esta compra.');
        }

        $result = $this->previewBuy($crypto, $usdAmount);

        return DB::transaction(function () use ($user, $portfolio, $crypto, $usdAmount, $result) {
            // 1) Guardar la simulación (vía repositorio)
            $simulation = $this->simulations->create(array_merge(
                $result->toArray(),
                ['user_id' => $user->id],
            ));

            // 2) Descontar el USD gastado
            $portfolio->decrement('usd_balance', $usdAmount);

            // 3) Sumar la cripto comprada (crea el asset si es la primera vez)
            $asset = $portfolio->assets()->firstOrCreate(
                ['cryptocurrency_id' => $crypto->id],
                ['balance' => 0],
            );
            $asset->increment('balance', $result->targetAmount);

            return $simulation;
        });
    }
}
