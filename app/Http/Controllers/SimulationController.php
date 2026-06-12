<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\SimulationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimulationController extends Controller
{
    public function __construct(private readonly SimulationService $simulations) {}

    /** Muestra el formulario de compra */
    public function buyForm()
    {
        $user = auth()->user();

        return Inertia::render('Simulations/Buy', [
            'cryptocurrencies' => Cryptocurrency::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'symbol', 'api_id']),
            'usdBalance' => (float) $user->portfolio->usd_balance,
        ]);
    }

    /** Calcula el preview SIN guardar (RN-02) */
    public function buyPreview(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
            'usd_amount'        => ['required', 'numeric', 'min:0.01'],
        ]);

        $crypto = Cryptocurrency::findOrFail($data['cryptocurrency_id']);
        $result = $this->simulations->previewBuy($crypto, (float) $data['usd_amount']);

        return response()->json([
            'price_usd'  => $result->targetPriceUsd,
            'quantity'   => $result->targetAmount,
            'usd_amount' => $result->usdEquivalent,
            'symbol'     => $crypto->symbol,
        ]);
    }

    /** Guarda la compra y actualiza el portafolio */
    public function buyStore(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
            'usd_amount'        => ['required', 'numeric', 'min:0.01'],
        ]);

        $crypto = Cryptocurrency::findOrFail($data['cryptocurrency_id']);

        try {
            $this->simulations->executeBuy($request->user(), $crypto, (float) $data['usd_amount']);
        } catch (\DomainException $e) {
            return back()->withErrors(['usd_amount' => $e->getMessage()]);
        }

        return redirect()
            ->route('simulations.buy.form')
            ->with('success', "Compra simulada de {$crypto->symbol} guardada correctamente.");
    }

    /** Muestra el formulario de venta (solo criptos que el usuario posee) */
    public function sellForm()
    {
        $user = auth()->user();

        $holdings = $user->portfolio->assets()
            ->with('cryptocurrency')
            ->where('balance', '>', 0)
            ->get()
            ->map(fn($a) => [
                'cryptocurrency_id' => $a->cryptocurrency_id,
                'name'              => $a->cryptocurrency->name,
                'symbol'            => $a->cryptocurrency->symbol,
                'balance'           => (float) $a->balance,
            ])
            ->values();

        return Inertia::render('Simulations/Sell', [
            'holdings'   => $holdings,
            'usdBalance' => (float) $user->portfolio->usd_balance,
        ]);
    }

    /** Preview de venta: USD + GTQ (RN-02) */
    public function sellPreview(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
            'quantity'          => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $crypto = Cryptocurrency::findOrFail($data['cryptocurrency_id']);
        $result = $this->simulations->previewSell($crypto, (float) $data['quantity']);

        return response()->json([
            'price_usd' => $result->sourcePriceUsd,
            'quantity'  => $result->sourceAmount,
            'usd_value' => $result->usdEquivalent,
            'gtq_value' => $result->gtqEquivalent,
            'symbol'    => $crypto->symbol,
        ]);
    }

    /** Guarda la venta */
    public function sellStore(Request $request)
    {
        $data = $request->validate([
            'cryptocurrency_id' => ['required', 'exists:cryptocurrencies,id'],
            'quantity'          => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $crypto = Cryptocurrency::findOrFail($data['cryptocurrency_id']);

        try {
            $this->simulations->executeSell($request->user(), $crypto, (float) $data['quantity']);
        } catch (\DomainException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('simulations.sell.form')
            ->with('success', "Venta simulada de {$crypto->symbol} guardada correctamente.");
    }

    /** Historial de simulaciones con filtro por tipo (RF-009) */
    public function history(Request $request)
    {
        $filters = ['type' => $request->query('type')];

        $paginator = $this->simulations->history($request->user()->id, $filters, 10);

        // Damos forma a cada fila para el frontend (sin exponer el modelo completo)
        $paginator->through(fn($sim) => [
            'id'               => $sim->id,
            'type'             => $sim->type,
            'source_symbol'    => $sim->sourceCrypto?->symbol,
            'target_symbol'    => $sim->targetCrypto?->symbol,
            'source_amount'    => $sim->source_amount,
            'target_amount'    => $sim->target_amount,
            'usd_equivalent'   => $sim->usd_equivalent,
            'created_at'       => $sim->created_at->format('Y-m-d H:i'),
        ]);

        return Inertia::render('Simulations/History', [
            'simulations' => $paginator,
            'filters'     => $filters,
        ]);
    }
}
