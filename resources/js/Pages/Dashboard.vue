<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useMoney } from '@/composables/useMoney';

const props = defineProps({
    usdBalance: { type: Number, default: 0 },
    holdings: { type: Array, default: () => [] },
    cryptoValue: { type: Number, default: 0 },
    totalValue: { type: Number, default: 0 },
});

const page = usePage();
const { money: usd } = useMoney();
const favorites = computed(() => page.props.favorites ?? []);

const palette = ['#f59e0b', '#00E599', '#22D3EE', '#8b5cf6', '#ef4444', '#0ea5e9'];

const composition = computed(() => {
    const total = props.holdings.reduce((sum, h) => sum + Number(h.value_usd), 0);
    return props.holdings.map((h, i) => ({
        ...h,
        pct: total > 0 ? (Number(h.value_usd) / total) * 100 : 0,
        color: palette[i % palette.length],
    }));
});

const cashPct = computed(() => (props.totalValue > 0 ? (props.usdBalance / props.totalValue) * 100 : 0));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Mi Portafolio</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- HERO: valor total -->
                <div class="relative overflow-hidden rounded-3xl border border-edge bg-surface p-7 shadow-card">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-neon/10 blur-3xl"></div>

                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Valor total del portafolio</p>
                            <p class="mt-1 text-4xl font-bold tabular-nums text-white sm:text-5xl">{{ usd(totalValue) }}</p>

                            <div class="mt-4 flex flex-wrap gap-x-8 gap-y-2">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Efectivo</p>
                                    <p class="font-semibold tabular-nums text-slate-200">{{ usd(usdBalance) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">En cripto</p>
                                    <p class="font-semibold tabular-nums text-slate-200">{{ usd(cryptoValue) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Link :href="route('simulations.buy.form')"
                                  class="rounded-xl bg-neon px-5 py-2.5 text-sm font-bold text-night shadow-neon transition hover:bg-neon-dark">
                                Comprar
                            </Link>
                            <Link :href="route('simulations.sell.form')"
                                  class="rounded-xl bg-loss px-5 py-2.5 text-sm font-bold text-night transition hover:opacity-90">
                                Vender
                            </Link>
                            <Link :href="route('simulations.exchange.form')"
                                  class="rounded-xl border border-edge bg-surface-2 px-5 py-2.5 text-sm font-semibold text-slate-200 transition hover:border-slate-600">
                                Intercambiar
                            </Link>
                        </div>
                    </div>

                    <!-- Barra: efectivo vs cripto -->
                    <div class="relative mt-6 flex h-2 w-full overflow-hidden rounded-full bg-surface-2">
                        <div class="h-full bg-slate-500" :style="{ width: cashPct + '%' }"></div>
                        <div class="h-full bg-neon" :style="{ width: (100 - cashPct) + '%' }"></div>
                    </div>
                    <div class="relative mt-2 flex justify-between text-xs text-slate-500">
                        <span>Efectivo {{ cashPct.toFixed(0) }}%</span>
                        <span>Cripto {{ (100 - cashPct).toFixed(0) }}%</span>
                    </div>
                </div>

                <!-- Composición en cripto -->
                <div v-if="composition.length" class="rounded-2xl border border-edge bg-surface p-6 shadow-card">
                    <h3 class="font-semibold text-white">Composición en criptomonedas</h3>
                    <p class="mb-4 text-xs text-slate-400">
                        Un intercambio cambia esta mezcla (mueve % de una cripto a otra) sin tocar tu saldo en efectivo.
                    </p>

                    <div class="flex h-5 w-full overflow-hidden rounded-full bg-surface-2">
                        <div v-for="c in composition" :key="c.symbol"
                             :style="{ width: c.pct + '%', backgroundColor: c.color }"
                             :title="`${c.symbol} ${c.pct.toFixed(1)}%`" class="h-full transition-all"></div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div v-for="c in composition" :key="c.symbol" class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full" :style="{ backgroundColor: c.color }"></span>
                                <span class="font-medium text-white">{{ c.symbol }}</span>
                                <span class="text-slate-500">{{ c.name }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="tabular-nums text-slate-300">{{ usd(c.value_usd) }}</span>
                                <span class="w-14 text-right font-semibold tabular-nums text-white">{{ c.pct.toFixed(1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Holdings -->
                <div class="overflow-hidden rounded-2xl border border-edge bg-surface shadow-card">
                    <div class="border-b border-edge px-6 py-4">
                        <h3 class="font-semibold text-white">Mis criptomonedas</h3>
                    </div>
                    <table class="min-w-full divide-y divide-edge text-sm">
                        <thead class="bg-surface-2/50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-400">Activo</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-400">Cantidad</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-400">Precio actual</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-400">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-edge/60">
                            <tr v-for="h in holdings" :key="h.symbol" class="hover:bg-surface-2/40">
                                <td class="px-6 py-3">
                                    <span v-if="favorites.includes(h.id)" class="mr-1 text-amber-400" title="Favorita">★</span>
                                    <span class="font-semibold text-white">{{ h.symbol }}</span>
                                    <span class="ml-1 text-slate-500">{{ h.name }}</span>
                                </td>
                                <td class="px-6 py-3 text-right tabular-nums text-slate-300">{{ Number(h.balance).toFixed(8) }}</td>
                                <td class="px-6 py-3 text-right tabular-nums text-slate-300">{{ usd(h.price_usd) }}</td>
                                <td class="px-6 py-3 text-right font-medium tabular-nums text-white">{{ usd(h.value_usd) }}</td>
                            </tr>
                            <tr v-if="holdings.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    Aún no tienes criptomonedas.
                                    <Link :href="route('simulations.buy.form')" class="text-neon underline">Simula tu primera compra</Link>.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
