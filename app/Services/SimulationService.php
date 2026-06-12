<?php

namespace App\Services;

use App\DTOs\SimulationResultData;
use App\Models\Cryptocurrency;
use App\Models\Simulation;
use App\Models\User;
use App\Repositories\Contracts\SimulationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
            type: 'BUY',
            sourceCryptoId: null,
            targetCryptoId: $crypto->id,
            sourceAmount: null,
            targetAmount: $quantity,
            sourcePriceUsd: null,
            targetPriceUsd: $price,
            usdEquivalent: $usdAmount,
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
    /**
     * Calcula una venta SIN guardar. Devuelve USD y GTQ (RF-006).
     */
    public function previewSell(Cryptocurrency $crypto, float $quantity): SimulationResultData
    {
        $price    = $this->prices->getCurrentPrice($crypto);
        $gtqRate  = $this->prices->getExchangeRateUsdToGtq();
        $usdValue = $quantity * $price;
        $gtqValue = $usdValue * $gtqRate;

        return new SimulationResultData(
            type: 'SELL',
            sourceCryptoId: $crypto->id,
            targetCryptoId: null,
            sourceAmount: $quantity,
            targetAmount: null,
            sourcePriceUsd: $price,
            targetPriceUsd: null,
            usdEquivalent: $usdValue,
            gtqEquivalent: $gtqValue,
        );
    }

    /**
     * Ejecuta y GUARDA la venta: descuenta la cripto y suma el USD.
     */
    public function executeSell(User $user, Cryptocurrency $crypto, float $quantity): Simulation
    {
        $portfolio = $user->portfolio;
        $asset = $portfolio->assets()->where('cryptocurrency_id', $crypto->id)->first();

        if ($quantity <= 0) {
            throw new \DomainException('La cantidad debe ser mayor a cero.');
        }
        // RN-03: no se puede vender más de lo disponible
        if (! $asset || (float) $asset->balance < $quantity) {
            throw new \DomainException('No tienes suficiente ' . $crypto->symbol . ' para vender.');
        }

        $result = $this->previewSell($crypto, $quantity);

        return DB::transaction(function () use ($user, $portfolio, $asset, $quantity, $result) {
            $simulation = $this->simulations->create(array_merge(
                $result->toArray(),
                ['user_id' => $user->id],
            ));

            $asset->decrement('balance', $quantity);                 // saco la cripto
            $portfolio->increment('usd_balance', $result->usdEquivalent); // entra el USD

            return $simulation;
        });
    }

    /**
     * Historial paginado de simulaciones del usuario, con filtros (RF-009).
     */
    public function history(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->simulations->paginateForUser($userId, $filters, $perPage);
    }
}
