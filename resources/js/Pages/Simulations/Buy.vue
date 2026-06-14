<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cryptocurrencies: Array,
    usdBalance: Number,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    cryptocurrency_id: props.cryptocurrencies[0]?.id ?? null,
    usd_amount: '',
});

const prices = ref({});
const loadingPrice = ref(false);

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);

const selected = computed(() => props.cryptocurrencies.find((c) => c.id === form.cryptocurrency_id));
const price = computed(() => Number(prices.value[form.cryptocurrency_id] ?? 0));
const amountNum = computed(() => Number(form.usd_amount) || 0);
const quantity = computed(() => (price.value > 0 ? amountNum.value / price.value : 0));
const overBalance = computed(() => amountNum.value > Number(props.usdBalance));
const canSubmit = computed(() => amountNum.value > 0 && !overBalance.value && price.value > 0);

const quickAmounts = [100, 500, 1000, 5000];

async function fetchPrice() {
    if (!form.cryptocurrency_id) return;
    loadingPrice.value = true;
    try {
        const { data } = await window.axios.get('/prices/current', { params: { ids: [form.cryptocurrency_id] } });
        prices.value = { ...prices.value, ...data.prices };
    } catch (e) { /* silencioso */ } finally {
        loadingPrice.value = false;
    }
}

onMounted(fetchPrice);
watch(() => form.cryptocurrency_id, fetchPrice);

function setQuick(v) { form.usd_amount = v; }
function setMax() { form.usd_amount = Number(props.usdBalance).toFixed(2); }

function comprar() {
    if (!canSubmit.value) return;
    form.post('/simulations/buy', {
        preserveScroll: true,
        onSuccess: () => { form.reset('usd_amount'); fetchPrice(); },
    });
}
</script>

<template>
    <Head title="Comprar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Comprar</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div v-if="flashSuccess"
                     class="rounded-xl border border-neon/30 bg-neon/10 p-4 text-sm font-medium text-neon">
                    {{ flashSuccess }}
                </div>

                <div class="overflow-hidden rounded-2xl border border-edge bg-surface shadow-card">
                    <!-- Encabezado con precio en vivo -->
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
                        <!-- Selector de activo -->
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-slate-500">Activo</label>
                            <select v-model="form.cryptocurrency_id"
                                    class="block w-full rounded-xl border-edge bg-surface-2 py-3 text-base font-semibold text-white shadow-sm focus:border-neon focus:ring-neon">
                                <option v-for="c in cryptocurrencies" :key="c.id" :value="c.id">{{ c.name }} ({{ c.symbol }})</option>
                            </select>
                        </div>

                        <!-- Monto -->
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Pagas (USD)</label>
                                <span class="text-xs text-slate-500">Saldo: {{ usd(usdBalance) }}</span>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg text-slate-500">$</span>
                                <input v-model="form.usd_amount" type="number" min="0" step="0.01" placeholder="0.00"
                                       class="block w-full rounded-xl border-edge bg-surface-2 py-3.5 pl-9 pr-4 text-2xl font-bold tabular-nums text-white placeholder-slate-600 shadow-sm focus:border-neon focus:ring-neon" />
                            </div>

                            <!-- Montos rápidos -->
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button v-for="q in quickAmounts" :key="q" type="button" @click="setQuick(q)"
                                        class="rounded-lg border border-edge bg-surface-2 px-3 py-1 text-xs font-semibold text-slate-300 hover:border-neon/50 hover:text-neon">
                                    ${{ q.toLocaleString() }}
                                </button>
                                <button type="button" @click="setMax"
                                        class="rounded-lg border border-edge bg-surface-2 px-3 py-1 text-xs font-semibold text-slate-300 hover:border-neon/50 hover:text-neon">
                                    Máx
                                </button>
                            </div>
                            <p v-if="overBalance" class="mt-2 text-sm text-loss">Saldo insuficiente.</p>
                            <p v-if="form.errors.usd_amount" class="mt-1 text-sm text-loss">{{ form.errors.usd_amount }}</p>
                        </div>

                        <!-- Resultado en vivo -->
                        <div class="rounded-xl border border-edge bg-night/40 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Recibirás (aprox.)</p>
                            <p class="mt-1 text-2xl font-bold tabular-nums text-neon">
                                {{ quantity.toFixed(8) }} <span class="text-base text-slate-400">{{ selected?.symbol }}</span>
                            </p>
                        </div>

                        <!-- CTA -->
                        <button @click="comprar" :disabled="!canSubmit || form.processing"
                                class="w-full rounded-xl bg-neon py-3.5 text-base font-bold text-night shadow-neon transition hover:bg-neon-dark disabled:cursor-not-allowed disabled:opacity-40">
                            {{ form.processing ? 'Procesando...' : `Comprar ${selected?.symbol ?? ''}` }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
