<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use App\Models\PriceSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoPriceService
{
    private string $baseUrl;
    private float $usdGtqFallback;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.coingecko.base_url'), '/');
        $this->usdGtqFallback = (float) config('services.fx.usd_gtq');
    }

    /**
     * Datos de mercado (precio USD, market cap, volumen) de una o varias criptos.
     * Cachea 5 minutos. En cada consulta REAL a CoinGecko guarda un snapshot.
     */
    public function getMarketData(array $apiIds): array
    {
        sort($apiIds);
        $cacheKey = 'crypto_prices:' . implode(',', $apiIds);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($apiIds) {
            $response = Http::timeout(10)->get($this->baseUrl . '/simple/price', [
                'ids'                => implode(',', $apiIds),
                'vs_currencies'      => 'usd',
                'include_market_cap' => 'true',
                'include_24hr_vol'   => 'true',
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('No se pudo obtener el precio desde CoinGecko.');
            }

            $data = $response->json();

            // Guardar snapshot de cada cripto consultada (RN-05 / RN-06)
            $gtqRate = $this->getExchangeRateUsdToGtq();
            foreach ($data as $apiId => $values) {
                $crypto = Cryptocurrency::where('api_id', $apiId)->first();
                if ($crypto) {
                    $this->saveSnapshot($crypto, $values, $gtqRate);
                }
            }

            return $data;
        });
    }

    /**
     * Precio actual de UNA cripto en USD.
     */
    public function getCurrentPrice(Cryptocurrency $crypto): float
    {
        $data = $this->getMarketData([$crypto->api_id]);

        return (float) ($data[$crypto->api_id]['usd'] ?? 0);
    }

    /**
     * Alias semántico: precio (puede venir de caché).
     */
    public function getCachedPrice(Cryptocurrency $crypto): float
    {
        return $this->getCurrentPrice($crypto);
    }

    /**
     * Convierte un monto de una cripto a otra (para el intercambio - RF-007).
     */
    public function convertCrypto(Cryptocurrency $source, Cryptocurrency $target, float $amount): array
    {
        $data = $this->getMarketData([$source->api_id, $target->api_id]);

        $sourcePrice = (float) ($data[$source->api_id]['usd'] ?? 0);
        $targetPrice = (float) ($data[$target->api_id]['usd'] ?? 0);

        $usdValue     = $amount * $sourcePrice;
        $targetAmount = $targetPrice > 0 ? $usdValue / $targetPrice : 0;

        return [
            'source_price_usd' => $sourcePrice,
            'target_price_usd' => $targetPrice,
            'usd_equivalent'   => $usdValue,
            'target_amount'    => $targetAmount,
        ];
    }

    /**
     * Tipo de cambio USD -> GTQ. Cachea 1 hora. Con respaldo si la API falla.
     */
    public function getExchangeRateUsdToGtq(): float
    {
        return Cache::remember('fx_usd_gtq', now()->addHour(), function () {
            try {
                $response = Http::timeout(8)->get('https://open.er-api.com/v6/latest/USD');
                if ($response->ok() && isset($response['rates']['GTQ'])) {
                    return (float) $response['rates']['GTQ'];
                }
            } catch (\Throwable $e) {
                Log::warning('FX USD/GTQ falló, usando respaldo: ' . $e->getMessage());
            }

            return $this->usdGtqFallback;
        });
    }

    /**
     * Guarda un registro histórico de precio (price_snapshots).
     */
    public function saveSnapshot(Cryptocurrency $crypto, array $values, ?float $gtqRate = null): PriceSnapshot
    {
        $gtqRate ??= $this->getExchangeRateUsdToGtq();
        $priceUsd = (float) ($values['usd'] ?? 0);

        return PriceSnapshot::create([
            'cryptocurrency_id' => $crypto->id,
            'price_usd'         => $priceUsd,
            'price_gtq'         => $priceUsd * $gtqRate,
            'market_cap'        => $values['usd_market_cap'] ?? null,
            'volume_24h'        => $values['usd_24h_vol'] ?? null,
            'captured_at'       => now(),
        ]);
    }

    /**
     * Snapshot más cercano a una fecha (para históricos guardados).
     */
    public function getSnapshotByDate(Cryptocurrency $crypto, string $date): ?PriceSnapshot
    {
        return $crypto->priceSnapshots()
            ->whereDate('captured_at', $date)
            ->latest('captured_at')
            ->first();
    }

        /**
     * Precios históricos de una cripto desde CoinGecko (market_chart).
     * Cachea 24 h (RF-004 / §14.7). Devuelve [{ t: ms, price: float }, ...].
     */
    public function getHistoricalPrice(Cryptocurrency $crypto, int $days = 7): array
    {
        $cacheKey = "crypto_history:{$crypto->api_id}:{$days}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($crypto, $days) {
            $response = Http::timeout(15)->get($this->baseUrl . "/coins/{$crypto->api_id}/market_chart", [
                'vs_currency' => 'usd',
                'days'        => $days,
            ]);

            if ($response->failed()) {
                return [];
            }

            // CoinGecko devuelve 'prices' => [[timestamp_ms, precio], ...]
            return collect($response->json('prices') ?? [])
                ->map(fn ($p) => ['t' => $p[0], 'price' => $p[1]])
                ->all();
        });
    }

}
