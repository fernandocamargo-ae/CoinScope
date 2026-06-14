<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cryptocurrencies: Array,
});

const selectedId = ref(props.cryptocurrencies[0]?.id ?? null);
const days = ref(7);
const series = ref([]);
const symbol = ref('');
const loading = ref(false);
const error = ref(null);

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);

const dayOptions = [
    { label: '24h', value: 1 },
    { label: '7d', value: 7 },
    { label: '30d', value: 30 },
    { label: '90d', value: 90 },
];

async function load() {
    if (!selectedId.value) return;
    loading.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get('/prices/history', {
            params: { cryptocurrency_id: selectedId.value, days: days.value },
        });
        series.value = data.series ?? [];
        symbol.value = data.symbol;
        if (series.value.length === 0) error.value = 'No hay datos históricos disponibles.';
    } catch (e) {
        error.value = 'No se pudieron cargar los datos históricos.';
    } finally {
        loading.value = false;
    }
}

function selectDays(d) {
    days.value = d;
    load();
}

onMounted(load);

// --- Gráfico SVG (sin librerías) ---
const W = 700;
const H = 240;
const PAD = 6;

const prices = computed(() => series.value.map((p) => p.price));
const minPrice = computed(() => (prices.value.length ? Math.min(...prices.value) : 0));
const maxPrice = computed(() => (prices.value.length ? Math.max(...prices.value) : 0));
const lastPrice = computed(() => (prices.value.length ? prices.value[prices.value.length - 1] : 0));
const firstPrice = computed(() => (prices.value.length ? prices.value[0] : 0));
const changePct = computed(() =>
    firstPrice.value ? ((lastPrice.value - firstPrice.value) / firstPrice.value) * 100 : 0
);
const isUp = computed(() => changePct.value >= 0);
const strokeColor = computed(() => (isUp.value ? '#00E599' : '#FF5C6C'));

const points = computed(() => {
    const n = series.value.length;
    if (n < 2) return [];
    const min = minPrice.value;
    const range = maxPrice.value - min || 1;
    return series.value.map((p, i) => {
        const x = PAD + (i / (n - 1)) * (W - 2 * PAD);
        const y = PAD + (1 - (p.price - min) / range) * (H - 2 * PAD);
        return [x, y];
    });
});

const linePath = computed(() =>
    points.value.map((pt, i) => (i === 0 ? 'M' : 'L') + pt[0].toFixed(1) + ' ' + pt[1].toFixed(1)).join(' ')
);

const areaPath = computed(() => {
    if (!points.value.length) return '';
    const lastX = points.value[points.value.length - 1][0].toFixed(1);
    const firstX = points.value[0][0].toFixed(1);
    return `${linePath.value} L ${lastX} ${H - PAD} L ${firstX} ${H - PAD} Z`;
});
</script>

<template>
    <Head title="Precios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Precios Históricos</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="rounded-2xl border border-edge bg-surface p-6 shadow-card space-y-5">

                    <!-- Selector de cripto + rango -->
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <select v-model="selectedId" @change="load"
                                class="rounded-lg border-edge bg-surface-2 text-sm text-slate-100 shadow-sm focus:border-neon focus:ring-neon">
                            <option v-for="c in cryptocurrencies" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.symbol }})
                            </option>
                        </select>

                        <div class="flex gap-1 rounded-lg border border-edge bg-surface-2 p-1">
                            <button v-for="o in dayOptions" :key="o.value" @click="selectDays(o.value)"
                                    :class="days === o.value ? 'bg-neon text-night' : 'text-slate-400 hover:text-white'"
                                    class="rounded-md px-3 py-1 text-sm font-semibold transition">
                                {{ o.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Precio actual + variación -->
                    <div v-if="series.length" class="flex items-baseline gap-3">
                        <span class="text-3xl font-bold tabular-nums text-white">{{ usd(lastPrice) }}</span>
                        <span :class="isUp ? 'text-neon' : 'text-loss'" class="text-sm font-semibold">
                            {{ isUp ? '▲' : '▼' }} {{ Math.abs(changePct).toFixed(2) }}%
                        </span>
                        <span class="text-xs text-slate-500">en {{ dayOptions.find((o) => o.value === days)?.label }}</span>
                    </div>

                    <!-- Gráfico -->
                    <div class="min-h-[240px]">
                        <p v-if="loading" class="py-24 text-center text-slate-500">Cargando datos...</p>
                        <p v-else-if="error" class="py-24 text-center text-slate-500">{{ error }}</p>
                        <svg v-else-if="points.length" :viewBox="`0 0 ${W} ${H}`"
                             preserveAspectRatio="none" class="w-full" style="height: 240px">
                            <defs>
                                <linearGradient id="priceGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" :stop-color="strokeColor" stop-opacity="0.30" />
                                    <stop offset="100%" :stop-color="strokeColor" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <path :d="areaPath" fill="url(#priceGrad)" />
                            <path :d="linePath" fill="none" :stroke="strokeColor" stroke-width="2"
                                  stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>

                    <!-- Mín / Máx -->
                    <div v-if="series.length" class="flex justify-between text-xs text-slate-500">
                        <span>Mín: <strong class="text-slate-300">{{ usd(minPrice) }}</strong></span>
                        <span>Máx: <strong class="text-slate-300">{{ usd(maxPrice) }}</strong></span>
                    </div>
                </div>

                <p class="text-center text-xs text-slate-600">
                    Datos históricos provistos por CoinGecko · en caché 24 h para respetar límites de la API.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
