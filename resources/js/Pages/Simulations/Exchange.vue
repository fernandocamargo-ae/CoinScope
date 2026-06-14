<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    holdings: Array,
    targets: Array,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    source_crypto_id: props.holdings[0]?.cryptocurrency_id ?? null,
    target_crypto_id: null,
    amount: '',
});

const prices = ref({});

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);

const availableTargets = computed(() => props.targets.filter((t) => t.id !== form.source_crypto_id));
form.target_crypto_id = availableTargets.value[0]?.id ?? null;

const selectedSource = computed(() => props.holdings.find((h) => h.cryptocurrency_id === form.source_crypto_id));
const targetMeta = computed(() => props.targets.find((t) => t.id === form.target_crypto_id));
const srcPrice = computed(() => Number(prices.value[form.source_crypto_id] ?? 0));
const tgtPrice = computed(() => Number(prices.value[form.target_crypto_id] ?? 0));
const amountNum = computed(() => Number(form.amount) || 0);
const usdValue = computed(() => amountNum.value * srcPrice.value);
const targetAmount = computed(() => (tgtPrice.value > 0 ? usdValue.value / tgtPrice.value : 0));
const overBalance = computed(() => selectedSource.value && amountNum.value > Number(selectedSource.value.balance));
const canSubmit = computed(() => amountNum.value > 0 && !overBalance.value && srcPrice.value > 0 && tgtPrice.value > 0);

const percents = [25, 50, 75, 100];

async function fetchPrices() {
    const ids = [form.source_crypto_id, form.target_crypto_id].filter(Boolean);
    if (!ids.length) return;
    try {
        const { data } = await window.axios.get('/prices/current', { params: { ids } });
        prices.value = { ...prices.value, ...data.prices };
    } catch (e) { /* silencioso */ }
}

onMounted(fetchPrices);
watch(() => form.source_crypto_id, () => {
    if (!availableTargets.value.some((t) => t.id === form.target_crypto_id)) {
        form.target_crypto_id = availableTargets.value[0]?.id ?? null;
    }
    fetchPrices();
});
watch(() => form.target_crypto_id, fetchPrices);

function setPercent(p) {
    if (selectedSource.value) form.amount = (Number(selectedSource.value.balance) * p / 100).toFixed(8);
}

function intercambiar() {
    if (!canSubmit.value) return;
    form.post('/simulations/exchange', {
        preserveScroll: true,
        onSuccess: () => { form.reset('amount'); fetchPrices(); },
    });
}
</script>

<template>
    <Head title="Intercambiar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Intercambiar</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div v-if="flashSuccess"
                     class="rounded-xl border border-neon/30 bg-neon/10 p-4 text-sm font-medium text-neon">
                    {{ flashSuccess }}
                </div>

                <div v-if="holdings.length === 0"
                     class="rounded-2xl border border-edge bg-surface p-6 text-slate-300 shadow-card">
                    No tienes criptomonedas para intercambiar todavía.
                    <a href="/simulations/buy" class="text-neon underline">Simula una compra primero</a>.
                </div>

                <div v-else class="overflow-hidden rounded-2xl border border-edge bg-surface p-6 shadow-card">
                    <!-- Origen -->
                    <div class="rounded-xl border border-edge bg-surface-2 p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Entregas</span>
                            <span class="text-xs text-slate-500">Disponible: {{ Number(selectedSource?.balance ?? 0).toFixed(8) }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input v-model="form.amount" type="number" min="0" step="0.00000001" placeholder="0.00000000"
                                   class="w-full border-0 bg-transparent p-0 text-2xl font-bold tabular-nums text-white placeholder-slate-600 focus:ring-0" />
                            <select v-model="form.source_crypto_id"
                                    class="shrink-0 rounded-lg border-edge bg-surface py-2 text-sm font-semibold text-white focus:border-neon focus:ring-neon">
                                <option v-for="h in holdings" :key="h.cryptocurrency_id" :value="h.cryptocurrency_id">{{ h.symbol }}</option>
                            </select>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button v-for="p in percents" :key="p" type="button" @click="setPercent(p)"
                                    class="rounded-lg border border-edge bg-surface px-3 py-1 text-xs font-semibold text-slate-300 hover:border-neon/50 hover:text-neon">
                                {{ p }}%
                            </button>
                        </div>
                    </div>

                    <!-- Flecha -->
                    <div class="relative my-2 flex justify-center">
                        <span class="grid h-9 w-9 place-items-center rounded-full border border-edge bg-surface-2 text-neon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12l7 7 7-7" />
                            </svg>
                        </span>
                    </div>

                    <!-- Destino -->
                    <div class="rounded-xl border border-edge bg-night/40 p-4">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Recibes (aprox.)</span>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="w-full text-2xl font-bold tabular-nums text-neon">{{ targetAmount.toFixed(8) }}</span>
                            <select v-model="form.target_crypto_id"
                                    class="shrink-0 rounded-lg border-edge bg-surface py-2 text-sm font-semibold text-white focus:border-neon focus:ring-neon">
                                <option v-for="t in availableTargets" :key="t.id" :value="t.id">{{ t.symbol }}</option>
                            </select>
                        </div>
                    </div>

                    <p class="mt-3 text-center text-xs text-slate-500">
                        Valor equivalente: <span class="text-slate-300">{{ usd(usdValue) }}</span>
                        <span v-if="targetMeta"> · 1 {{ selectedSource?.symbol }} ≈ {{ tgtPrice > 0 ? (srcPrice / tgtPrice).toFixed(6) : '—' }} {{ targetMeta.symbol }}</span>
                    </p>

                    <p v-if="overBalance" class="mt-2 text-center text-sm text-loss">No tienes suficiente {{ selectedSource?.symbol }}.</p>
                    <p v-if="form.errors.amount" class="mt-1 text-center text-sm text-loss">{{ form.errors.amount }}</p>

                    <!-- CTA -->
                    <button @click="intercambiar" :disabled="!canSubmit || form.processing"
                            class="mt-5 w-full rounded-xl bg-neon py-3.5 text-base font-bold text-night shadow-neon transition hover:bg-neon-dark disabled:cursor-not-allowed disabled:opacity-40">
                        {{ form.processing ? 'Procesando...' : `Intercambiar ${selectedSource?.symbol ?? ''} por ${targetMeta?.symbol ?? ''}` }}
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
