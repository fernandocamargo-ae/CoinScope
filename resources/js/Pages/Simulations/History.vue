<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    simulations: Object, // paginador: { data, links, ... }
    filters: Object,
});

const type = ref(props.filters.type ?? '');

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);

function aplicarFiltro() {
    router.get('/simulations/history',
        { type: type.value || undefined },
        { preserveState: true, replace: true },
    );
}

const badge = (t) => ({
    BUY: 'bg-green-100 text-green-800',
    SELL: 'bg-red-100 text-red-800',
    EXCHANGE: 'bg-blue-100 text-blue-800',
}[t] ?? 'bg-gray-100 text-gray-800');

// Texto descriptivo según el tipo de operación
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Historial de Simulaciones</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-4">

                <!-- Filtro -->
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Filtrar por tipo:</label>
                    <select v-model="type" @change="aplicarFiltro"
                        class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="BUY">Compra</option>
                        <option value="SELL">Venta</option>
                        <option value="EXCHANGE">Intercambio</option>
                    </select>
                </div>

                <!-- Tabla -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Tipo</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Detalle</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">USD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="s in simulations.data" :key="s.id">
                                <td class="px-4 py-3 text-gray-600">{{ s.created_at }}</td>
                                <td class="px-4 py-3">
                                    <span :class="badge(s.type)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ s.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ detalle(s) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ usd(s.usd_equivalent) }}
                                </td>
                            </tr>
                            <tr v-if="simulations.data.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                    No hay simulaciones que mostrar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="simulations.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="(link, i) in simulations.links" :key="i">
                        <span v-if="!link.url" class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                        <Link v-else :href="link.url" preserve-state replace class="px-3 py-1 text-sm rounded border"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            v-html="link.label" />
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
