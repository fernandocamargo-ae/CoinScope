<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { margin: 0; color: #1f2937; font-size: 12px; }

        .header { background-color: #4f46e5; color: #ffffff; padding: 22px 28px; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 0.5px; }
        .header .sub { margin-top: 4px; font-size: 11px; color: #c7d2fe; }

        .content { padding: 20px 28px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 11px; color: #4b5563; }
        .meta td { padding: 2px 0; }
        .meta .label { color: #9ca3af; width: 110px; }

        .cards { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 20px; }
        .card { background-color: #f3f4f6; border-radius: 6px; padding: 10px 12px; text-align: center; }
        .card .num { font-size: 16px; font-weight: bold; color: #111827; }
        .card .cap { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        .card.total { background-color: #eef2ff; }
        .card.total .num { color: #4f46e5; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background-color: #f9fafb; text-align: left; padding: 8px; font-size: 9px;
                        color: #6b7280; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        table.data td { padding: 7px 8px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }

        .badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-BUY { background-color: #dcfce7; color: #166534; }
        .badge-SELL { background-color: #fee2e2; color: #991b1b; }
        .badge-EXCHANGE { background-color: #dbeafe; color: #1e40af; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb;
                  font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CoinScope</h1>
        <div class="sub">Reporte de Simulaciones de Criptomonedas</div>
    </div>

    <div class="content">
        <table class="meta">
            <tr><td class="label">Usuario:</td><td>{{ $user->name }} &lt;{{ $user->email }}&gt;</td></tr>
            <tr><td class="label">Generado:</td><td>{{ $generatedAt }}</td></tr>
            <tr><td class="label">Filtro:</td><td>{{ $type ?: 'Todas las operaciones' }}</td></tr>
        </table>

        <table class="cards">
            <tr>
                <td class="card"><div class="num">{{ $totals['count'] }}</div><div class="cap">Operaciones</div></td>
                <td class="card"><div class="num">{{ $totals['buy'] }}</div><div class="cap">Compras</div></td>
                <td class="card"><div class="num">{{ $totals['sell'] }}</div><div class="cap">Ventas</div></td>
                <td class="card"><div class="num">{{ $totals['exchange'] }}</div><div class="cap">Intercambios</div></td>
                <td class="card total"><div class="num">${{ number_format($totals['usd'], 2) }}</div><div class="cap">USD movido</div></td>
            </tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th style="width: 110px;">Fecha</th>
                    <th style="width: 80px;">Tipo</th>
                    <th>Detalle</th>
                    <th class="text-right" style="width: 90px;">USD</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($simulations as $s)
                    <tr>
                        <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge badge-{{ $s->type }}">{{ ['BUY' => 'COMPRA', 'SELL' => 'VENTA', 'EXCHANGE' => 'INTERCAMBIO'][$s->type] ?? $s->type }}</span></td>
                        <td>
                            @if ($s->type === 'BUY')
                                Compró {{ rtrim(rtrim(number_format($s->target_amount, 8), '0'), '.') }} {{ optional($s->targetCrypto)->symbol }}
                            @elseif ($s->type === 'SELL')
                                Vendió {{ rtrim(rtrim(number_format($s->source_amount, 8), '0'), '.') }} {{ optional($s->sourceCrypto)->symbol }}
                            @else
                                {{ rtrim(rtrim(number_format($s->source_amount, 8), '0'), '.') }} {{ optional($s->sourceCrypto)->symbol }}
                                &rarr;
                                {{ rtrim(rtrim(number_format($s->target_amount, 8), '0'), '.') }} {{ optional($s->targetCrypto)->symbol }}
                            @endif
                        </td>
                        <td class="text-right">${{ number_format($s->usd_equivalent, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:16px;">Sin operaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            CoinScope · Reporte generado automáticamente.<br>
            Las simulaciones son virtuales y no representan operaciones financieras reales.
        </div>
    </div>
</body>
</html>
