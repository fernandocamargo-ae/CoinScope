<?php

namespace App\Http\Controllers;

use App\Services\CryptoPriceService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly CryptoPriceService $prices) {}

    public function __invoke()
    {
        $user      = auth()->user();
        $portfolio = $user->portfolio;

        // Solo las criptos con saldo > 0
        $assets = $portfolio->assets()
            ->with('cryptocurrency')
            ->where('balance', '>', 0)
            ->get();

        // Pedimos los precios actuales de TODAS sus criptos de una sola vez (1 llamada, cacheada)
        $apiIds = $assets->pluck('cryptocurrency.api_id')->all();
        $market = $apiIds ? $this->prices->getMarketData($apiIds) : [];

        // Valorizamos cada tenencia al precio actual
        $holdings = $assets->map(function ($a) use ($market) {
            $price   = (float) ($market[$a->cryptocurrency->api_id]['usd'] ?? 0);
            $balance = (float) $a->balance;

            return [
                'symbol'    => $a->cryptocurrency->symbol,
                'name'      => $a->cryptocurrency->name,
                'balance'   => $balance,
                'price_usd' => $price,
                'value_usd' => $balance * $price,
            ];
        });

        $cryptoValue = $holdings->sum('value_usd');
        $usdBalance  = (float) $portfolio->usd_balance;

        return Inertia::render('Dashboard', [
            'usdBalance'  => $usdBalance,
            'holdings'    => $holdings->values(),
            'cryptoValue' => $cryptoValue,
            'totalValue'  => $usdBalance + $cryptoValue,
        ]);
    }
}
