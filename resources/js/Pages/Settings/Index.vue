<script setup>
import { computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useMoney } from '@/composables/useMoney';

const props = defineProps({
    cryptocurrencies: Array,
    usdBalance: Number,
});

const page = usePage();
const { money } = useMoney();

const flashSuccess = computed(() => page.props.flash?.success);
const currency = computed(() => page.props.auth?.user?.display_currency ?? 'USD');
const favorites = computed(() => page.props.favorites ?? []);

// 4a — Moneda
function setCurrency(value) {
    router.patch('/settings/currency', { display_currency: value }, { preserveScroll: true });
}

// 4b — Fondear
const fundForm = useForm({ amount: '' });
function fund(amount) {
    fundForm.amount = amount ?? fundForm.amount;
    fundForm.post('/settings/fund', {
        preserveScroll: true,
        onSuccess: () => fundForm.reset('amount'),
    });
}

// 4b — Reiniciar
function reset() {
    if (confirm('¿Seguro que quieres reiniciar tu portafolio? Tu saldo volverá a $100,000 y se borrarán tus criptos (el historial se conserva).')) {
        router.post('/settings/reset', {}, { preserveScroll: true });
    }
}

// 4c — Favoritas
function toggleFavorite(id) {
    router.post('/settings/favorites/toggle', { cryptocurrency_id: id }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Ajustes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Ajustes del Portafolio</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div v-if="flashSuccess"
                     class="rounded-xl border border-neon/30 bg-neon/10 p-4 text-sm font-medium text-neon">
                    {{ flashSuccess }}
                </div>

                <!-- 4a · Moneda de visualización -->
                <section class="rounded-2xl border border-edge bg-surface p-6 shadow-card">
                    <h3 class="font-semibold text-white">Moneda de visualización</h3>
                    <p class="mt-1 text-sm text-slate-400">Elige en qué moneda se muestran los montos del panel.</p>

                    <div class="mt-4 inline-flex rounded-lg border border-edge bg-surface-2 p-1">
                        <button @click="setCurrency('USD')"
                                :class="currency === 'USD' ? 'bg-neon text-night' : 'text-slate-400 hover:text-white'"
                                class="rounded-md px-5 py-1.5 text-sm font-semibold transition">USD</button>
                        <button @click="setCurrency('GTQ')"
                                :class="currency === 'GTQ' ? 'bg-neon text-night' : 'text-slate-400 hover:text-white'"
                                class="rounded-md px-5 py-1.5 text-sm font-semibold transition">GTQ</button>
                    </div>
                </section>

                <!-- 4b · Saldo virtual -->
                <section class="rounded-2xl border border-edge bg-surface p-6 shadow-card">
                    <h3 class="font-semibold text-white">Saldo virtual</h3>
                    <p class="mt-1 text-sm text-slate-400">
                        Saldo actual en efectivo: <strong class="text-white">{{ money(usdBalance) }}</strong>
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <input v-model="fundForm.amount" type="number" min="1" step="0.01" placeholder="Monto USD a agregar"
                               class="w-48 rounded-lg border-edge bg-surface-2 text-slate-100 placeholder-slate-500 shadow-sm focus:border-neon focus:ring-neon" />
                        <button @click="fund()" :disabled="fundForm.processing"
                                class="rounded-lg bg-neon px-4 py-2 text-sm font-bold text-night shadow-neon transition hover:bg-neon-dark disabled:opacity-50">
                            Agregar
                        </button>
                        <button @click="fund(10000)" class="rounded-lg border border-edge bg-surface-2 px-3 py-2 text-sm text-slate-300 hover:border-slate-600">+$10,000</button>
                        <button @click="fund(50000)" class="rounded-lg border border-edge bg-surface-2 px-3 py-2 text-sm text-slate-300 hover:border-slate-600">+$50,000</button>
                    </div>
                    <p v-if="fundForm.errors.amount" class="mt-1 text-sm text-loss">{{ fundForm.errors.amount }}</p>

                    <div class="mt-5 border-t border-edge pt-4">
                        <button @click="reset"
                                class="rounded-lg border border-loss/40 bg-loss/10 px-4 py-2 text-sm font-semibold text-loss transition hover:bg-loss/20">
                            Reiniciar portafolio
                        </button>
                        <span class="ml-2 text-xs text-slate-500">Vuelve a $100,000 y borra tus criptos (conserva el historial).</span>
                    </div>
                </section>

                <!-- 4c · Criptos favoritas -->
                <section class="rounded-2xl border border-edge bg-surface p-6 shadow-card">
                    <h3 class="font-semibold text-white">Criptomonedas favoritas</h3>
                    <p class="mt-1 text-sm text-slate-400">Márcalas con la estrella para destacarlas en tu dashboard.</p>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <button v-for="c in cryptocurrencies" :key="c.id" @click="toggleFavorite(c.id)"
                                class="flex items-center justify-between rounded-lg border border-edge bg-surface-2 px-4 py-3 text-left transition hover:border-slate-600">
                            <span>
                                <span class="font-semibold text-white">{{ c.symbol }}</span>
                                <span class="ml-1 text-sm text-slate-500">{{ c.name }}</span>
                            </span>
                            <svg class="h-5 w-5" :class="favorites.includes(c.id) ? 'text-amber-400' : 'text-slate-600'"
                                 :fill="favorites.includes(c.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11.48 3.5l2.12 4.29 4.73.69-3.42 3.34.81 4.71-4.24-2.23-4.23 2.23.8-4.71L4.65 8.48l4.73-.69z" />
                            </svg>
                        </button>
                    </div>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
