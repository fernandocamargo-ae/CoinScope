<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Services\SimulationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulations,
        private readonly AuditService $audit,
    ) {}


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
            $this->audit->log('SIMULATE_BUY', "Compró {$crypto->symbol} por \${$data['usd_amount']}");
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
            $this->audit->log('SIMULATE_SELL', "Vendió {$data['quantity']} {$crypto->symbol}");
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
            'created_at'       => $sim->created_at->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Simulations/History', [
            'simulations' => $paginator,
            'filters'     => $filters,
        ]);
    }

    /** Formulario de intercambio */
    public function exchangeForm()
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

        $targets = Cryptocurrency::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'symbol']);

        return Inertia::render('Simulations/Exchange', [
            'holdings' => $holdings,
            'targets'  => $targets,
        ]);
    }

    /** Preview del intercambio (RN-02) */
    public function exchangePreview(Request $request)
    {
        $data = $request->validate([
            'source_crypto_id' => ['required', 'exists:cryptocurrencies,id'],
            'target_crypto_id' => ['required', 'different:source_crypto_id', 'exists:cryptocurrencies,id'],
            'amount'           => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $source = Cryptocurrency::findOrFail($data['source_crypto_id']);
        $target = Cryptocurrency::findOrFail($data['target_crypto_id']);
        $result = $this->simulations->previewExchange($source, $target, (float) $data['amount']);

        return response()->json([
            'source_symbol'    => $source->symbol,
            'target_symbol'    => $target->symbol,
            'source_amount'    => $result->sourceAmount,
            'target_amount'    => $result->targetAmount,
            'source_price_usd' => $result->sourcePriceUsd,
            'target_price_usd' => $result->targetPriceUsd,
            'usd_equivalent'   => $result->usdEquivalent,
        ]);
    }

    /** Guarda el intercambio */
    public function exchangeStore(Request $request)
    {
        $data = $request->validate([
            'source_crypto_id' => ['required', 'exists:cryptocurrencies,id'],
            'target_crypto_id' => ['required', 'different:source_crypto_id', 'exists:cryptocurrencies,id'],
            'amount'           => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $source = Cryptocurrency::findOrFail($data['source_crypto_id']);
        $target = Cryptocurrency::findOrFail($data['target_crypto_id']);

        try {
            $this->simulations->executeExchange($request->user(), $source, $target, (float) $data['amount']);
            $this->audit->log('SIMULATE_EXCHANGE', "Intercambió {$data['amount']} {$source->symbol} por {$target->symbol}");
        } catch (\DomainException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()
            ->route('simulations.exchange.form')
            ->with('success', "Intercambio simulado de {$source->symbol} a {$target->symbol} guardado correctamente.");
    }

    /** Exporta el historial de simulaciones a CSV (respeta el filtro de tipo) */
    public function export(Request $request)
    {
        $type     = $request->query('type');
        $filename = 'simulaciones_' . now()->format('Y-m-d') . '.csv';

        $simulations = \App\Models\Simulation::where('user_id', $request->user()->id)
            ->when($type, fn($q) => $q->where('type', $type))
            ->with(['sourceCrypto', 'targetCrypto'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($simulations) {
            $out = fopen('php://output', 'w');

            // BOM para que Excel reconozca acentos (UTF-8)
            fwrite($out, "\xEF\xBB\xBF");

            // Encabezados
            fputcsv($out, [
                'Fecha',
                'Tipo',
                'Origen',
                'Cantidad origen',
                'Destino',
                'Cantidad destino',
                'Precio origen USD',
                'Precio destino USD',
                'Equivalente USD',
            ]);

            // Filas
            foreach ($simulations as $s) {
                fputcsv($out, [
                    $s->created_at->format('d/m/Y H:i:s'),
                    $s->type,
                    $s->sourceCrypto?->symbol,
                    $s->source_amount,
                    $s->targetCrypto?->symbol,
                    $s->target_amount,
                    $s->source_price_usd,
                    $s->target_price_usd,
                    $s->usd_equivalent,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /** Exporta el historial a PDF con formato (Reportes / Objetivo 7) */
    public function exportPdf(Request $request)
    {
        $type = $request->query('type');

        $simulations = \App\Models\Simulation::where('user_id', $request->user()->id)
            ->when($type, fn($q) => $q->where('type', $type))
            ->with(['sourceCrypto', 'targetCrypto'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('reports.simulations', [
            'user'        => $request->user(),
            'simulations' => $simulations,
            'type'        => $type,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'totals'      => [
                'count'    => $simulations->count(),
                'buy'      => $simulations->where('type', 'BUY')->count(),
                'sell'     => $simulations->where('type', 'SELL')->count(),
                'exchange' => $simulations->where('type', 'EXCHANGE')->count(),
                'usd'      => $simulations->sum('usd_equivalent'),
            ],
        ]);

        return $pdf->download('reporte_simulaciones_' . now()->format('Y-m-d') . '.pdf');
    }
}
