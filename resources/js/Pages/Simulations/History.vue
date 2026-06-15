<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useMoney } from '@/composables/useMoney';

const props = defineProps({
    simulations: Object,
    filters: Object,
});

const type = ref(props.filters.type ?? '');

const exportUrl = computed(() => '/simulations/export' + (type.value ? `?type=${type.value}` : ''));
const exportPdfUrl = computed(() => '/simulations/export-pdf' + (type.value ? `?type=${type.value}` : ''));

const { money: usd } = useMoney();

function aplicarFiltro() {
    router.get('/simulations/history',
        { type: type.value || undefined },
        { preserveState: true, replace: true },
    );
}

const badge = (t) => ({
    BUY: 'bg-neon/15 text-neon',
    SELL: 'bg-loss/15 text-loss',
    EXCHANGE: 'bg-ice/15 text-ice',
}[t] ?? 'bg-surface-2 text-slate-300');

function detalle(s) {
    if (s.type === 'BUY') return `Compró ${Number(s.target_amount).toFixed(8)} ${s.target_symbol}`;
    if (s.type === 'SELL') return `Vendió ${Number(s.source_amount).toFixed(8)} ${s.source_symbol}`;
    return `${Number(s.source_amount).toFixed(8)} ${s.source_symbol} → ${Number(s.target_amount).toFixed(8)} ${s.target_symbol}`;
}
</script>

<template>
    <Head title="Historial" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Historial de Simulaciones</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Filtro + exportación -->
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-edge bg-surface p-4 shadow-card">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-slate-300">Filtrar por tipo:</label>
                        <select v-model="type" @change="aplicarFiltro"
                            class="rounded-lg border-edge bg-surface-2 text-sm text-slate-100 shadow-sm focus:border-neon focus:ring-neon">
                            <option value="">Todos</option>
                            <option value="BUY">Compra</option>
                            <option value="SELL">Venta</option>
                            <option value="EXCHANGE">Intercambio</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="exportUrl"
                            class="inline-flex items-center gap-2 rounded-lg border border-edge bg-surface-2 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-neon/50 hover:text-neon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            CSV
                        </a>
                        <a :href="exportPdfUrl"
                            class="inline-flex items-center gap-2 rounded-lg border border-edge bg-surface-2 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-loss/50 hover:text-loss">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto rounded-2xl border border-edge bg-surface shadow-card">
                    <table class="w-full min-w-[600px] divide-y divide-edge text-sm">
                        <thead class="bg-surface-2/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Tipo</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Detalle</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-400">USD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-edge/60">
                            <tr v-for="s in simulations.data" :key="s.id" class="hover:bg-surface-2/40">
                                <td class="px-4 py-3 text-slate-400">{{ s.created_at }}</td>
                                <td class="px-4 py-3">
                                    <span :class="badge(s.type)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ s.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ detalle(s) }}</td>
                                <td class="px-4 py-3 text-right font-medium tabular-nums text-white">{{ usd(s.usd_equivalent) }}</td>
                            </tr>
                            <tr v-if="simulations.data.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                    No hay simulaciones que mostrar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="simulations.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="(link, i) in simulations.links" :key="i">
                        <span v-if="!link.url" class="px-3 py-1 text-sm text-slate-600" v-html="link.label" />
                        <Link v-else :href="link.url" preserve-state replace
                              class="rounded border px-3 py-1 text-sm"
                              :class="link.active ? 'border-neon bg-neon text-night' : 'border-edge bg-surface-2 text-slate-300 hover:border-slate-600'"
                              v-html="link.label" />
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
