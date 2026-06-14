<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    private const INITIAL_BALANCE = 100000;

    public function __construct(private readonly AuditService $audit) {}

    /** Página de ajustes del portafolio */
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Settings/Index', [
            'cryptocurrencies' => Cryptocurrency::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'symbol']),
            'usdBalance' => (float) $user->portfolio->usd_balance,
        ]);
    }

    /** 4a — Moneda de visualización (USD / GTQ) */
    public function updateCurrency(Request $request)
    {
        $data = $request->validate([
            'display_currency' => ['required', 'in:USD,GTQ'],
        ]);

        $request->user()->update(['display_currency' => $data['display_currency']]);

        return back()->with('success', 'Moneda de visualización actualizada.');
    }

    /** 4b — Fondear: agregar USD virtuales */
    public function fund(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
        ]);

        $request->user()->portfolio->increment('usd_balance', $data['amount']);
        $this->audit->log('FUND_PORTFOLIO', "Fondeó \${$data['amount']} USD virtuales");

        return back()->with('success', 'Saldo agregado correctamente.');
    }

    /** 4b — Reiniciar el portafolio al estado inicial */
    public function reset(Request $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->portfolio->update(['usd_balance' => self::INITIAL_BALANCE]);
            $user->portfolio->assets()->delete();
        });

        $this->audit->log('RESET_PORTFOLIO', 'Reinició su portafolio al estado inicial');

        return back()->with('success', 'Portafolio reiniciado al estado inicial.');
    }

    /** 4c — Marcar / desmarcar una cripto como favorita */
    public function toggleFavorite(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
        ]);

        $request->user()->favorites()->toggle($data['cryptocurrency_id']);

        return back();
    }
}
