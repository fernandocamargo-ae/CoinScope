<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    holdings: Array,
    usdBalance: Number,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    cryptocurrency_id: props.holdings[0]?.cryptocurrency_id ?? null,
    quantity: '',
});

const prices = ref({});
const gtqRate = ref(7.75);
const loadingPrice = ref(false);

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);
const gtq = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(n ?? 0);

const selected = computed(() => props.holdings.find((h) => h.cryptocurrency_id === form.cryptocurrency_id));
const price = computed(() => Number(prices.value[form.cryptocurrency_id] ?? 0));
const qtyNum = computed(() => Number(form.quantity) || 0);
const usdValue = computed(() => qtyNum.value * price.value);
const gtqValue = computed(() => usdValue.value * gtqRate.value);
const overBalance = computed(() => selected.value && qtyNum.value > Number(selected.value.balance));
const canSubmit = computed(() => qtyNum.value > 0 && !overBalance.value && price.value > 0);

const percents = [25, 50, 75, 100];

async function fetchPrice() {
    if (!form.cryptocurrency_id) return;
    loadingPrice.value = true;
    try {
        const { data } = await window.axios.get('/prices/current', { params: { ids: [form.cryptocurrency_id] } });
        prices.value = { ...prices.value, ...data.prices };
        gtqRate.value = Number(data.gtq_rate) || gtqRate.value;
    } catch (e) { /* silencioso */ } finally {
        loadingPrice.value = false;
    }
}

onMounted(fetchPrice);
watch(() => form.cryptocurrency_id, fetchPrice);

function setPercent(p) {
    if (selected.value) form.quantity = (Number(selected.value.balance) * p / 100).toFixed(8);
}

function vender() {
    if (!canSubmit.value) return;
    form.post('/simulations/sell', {
        preserveScroll: true,
        onSuccess: () => { form.reset('quantity'); fetchPrice(); },
    });
}
</script>

<template>
    <Head title="Vender" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Vender</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div v-if="flashSuccess"
                     class="rounded-xl border border-neon/30 bg-neon/10 p-4 text-sm font-medium text-neon">
                    {{ flashSuccess }}
                </div>

                <div v-if="holdings.length === 0"
                     class="rounded-2xl border border-edge bg-surface p-6 text-slate-300 shadow-card">
                    No tienes criptomonedas para vender todavía.
                    <a href="/simulations/buy" class="text-neon underline">Simula una compra primero</a>.
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-edge bg-surface shadow-card">
                    <!-- Encabezado precio en vivo -->
                    <div class="flex items-center justify-between border-b border-edge px-6 py-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Precio actual</p>
                            <p class="text-lg font-bold tabular-nums text-white">
                                {{ price > 0 ? usd(price) : '—' }}
                                <span class="text-sm font-medium text-slate-500">/ {{ selected?.symbol }}</span>
                            </p>
                        </div>
                        <span class="flex items-center gap-1.5 rounded-full bg-neon/10 px-3 py-1 text-xs font-semibold text-neon">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-neon"></span>
                            En vivo
                        </span>
                    </div>

                    <div class="space-y-5 p-6">
                        <!-- Activo -->
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-slate-500">Activo</label>
                            <select v-model="form.cryptocurrency_id"
                                    class="block w-full rounded-xl border-edge bg-surface-2 py-3 text-base font-semibold text-white shadow-sm focus:border-neon focus:ring-neon">
                                <option v-for="h in holdings" :key="h.cryptocurrency_id" :value="h.cryptocurrency_id">{{ h.name }} ({{ h.symbol }})</option>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendes ({{ selected?.symbol }})</label>
                                <span class="text-xs text-slate-500">Disponible: {{ Number(selected?.balance ?? 0).toFixed(8) }}</span>
                            </div>
                            <input v-model="form.quantity" type="number" min="0" step="0.00000001" placeholder="0.00000000"
                                   class="block w-full rounded-xl border-edge bg-surface-2 py-3.5 px-4 text-2xl font-bold tabular-nums text-white placeholder-slate-600 shadow-sm focus:border-neon focus:ring-neon" />

                            <div class="mt-2 flex flex-wrap gap-2">
                                <button v-for="p in percents" :key="p" type="button" @click="setPercent(p)"
                                        class="rounded-lg border border-edge bg-surface-2 px-3 py-1 text-xs font-semibold text-slate-300 hover:border-neon/50 hover:text-neon">
                                    {{ p }}%
                                </button>
                            </div>
                            <p v-if="overBalance" class="mt-2 text-sm text-loss">No tienes suficiente {{ selected?.symbol }}.</p>
                            <p v-if="form.errors.quantity" class="mt-1 text-sm text-loss">{{ form.errors.quantity }}</p>
                        </div>

                        <!-- Resultado en vivo -->
                        <div class="rounded-xl border border-edge bg-night/40 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Recibirás (aprox.)</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-neon">{{ usd(usdValue) }}</p>
                            <p class="text-sm tabular-nums text-slate-400">≈ {{ gtq(gtqValue) }}</p>
                        </div>

                        <!-- CTA -->
                        <button @click="vender" :disabled="!canSubmit || form.processing"
                                class="w-full rounded-xl bg-loss py-3.5 text-base font-bold text-night transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
                            {{ form.processing ? 'Procesando...' : `Vender ${selected?.symbol ?? ''}` }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
