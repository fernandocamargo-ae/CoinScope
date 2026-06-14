<?php

namespace App\Http\Middleware;

use App\Services\CryptoPriceService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
            ],

            // Tipo de cambio USD->GTQ (cacheado) para mostrar montos en la moneda elegida
            'rates' => [
                'usd_gtq' => fn() => $request->user()
                    ? app(CryptoPriceService::class)->getExchangeRateUsdToGtq()
                    : null,
            ],

            // IDs de las criptomonedas marcadas como favoritas por el usuario
            'favorites' => fn() => $request->user()
                ? $request->user()->favorites()->pluck('cryptocurrencies.id')
                : [],
        ];
    }
}
