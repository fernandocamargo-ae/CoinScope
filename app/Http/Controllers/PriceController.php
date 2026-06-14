<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\CryptoPriceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PriceController extends Controller
{
    public function __construct(private readonly CryptoPriceService $prices) {}

    /** Página de precios históricos */
    public function index()
    {
        return Inertia::render('Prices/Index', [
            'cryptocurrencies' => Cryptocurrency::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'symbol', 'api_id']),
        ]);
    }

    /** Devuelve la serie histórica para el gráfico (JSON) */
    public function history(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
            'days'              => ['nullable', 'integer', 'in:1,7,30,90'],
        ]);

        $crypto = Cryptocurrency::findOrFail($data['cryptocurrency_id']);
        $days   = (int) ($data['days'] ?? 7);

        return response()->json([
            'symbol' => $crypto->symbol,
            'days'   => $days,
            'series' => $this->prices->getHistoricalPrice($crypto, $days),
        ]);
    }

    /** Precios actuales (USD) de una o varias criptos + tipo de cambio GTQ (para cálculo en vivo) */
    public function current(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cryptocurrencies,id'],
        ]);

        $cryptos = Cryptocurrency::whereIn('id', $data['ids'])->get();
        $market  = $this->prices->getMarketData($cryptos->pluck('api_id')->all());

        $prices = $cryptos->mapWithKeys(fn ($c) => [
            $c->id => (float) ($market[$c->api_id]['usd'] ?? 0),
        ]);

        return response()->json([
            'prices'   => $prices,
            'gtq_rate' => $this->prices->getExchangeRateUsdToGtq(),
        ]);
    }
}
