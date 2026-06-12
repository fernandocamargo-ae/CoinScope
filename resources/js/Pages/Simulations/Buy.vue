<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cryptocurrencies: Array,
    usdBalance: Number,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const preview = ref(null);
const previewError = ref(null);
const loadingPreview = ref(false);

const form = useForm({
    cryptocurrency_id: props.cryptocurrencies[0]?.id ?? null,
    usd_amount: '',
});

const money = (n) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);

async function calcular() {
    previewError.value = null;
    preview.value = null;

    if (!form.cryptocurrency_id || !form.usd_amount) {
        previewError.value = 'Selecciona una cripto e ingresa un monto.';
        return;
    }

    loadingPreview.value = true;
    try {
        const { data } = await window.axios.get('/simulations/buy/preview', {
            params: {
                cryptocurrency_id: form.cryptocurrency_id,
                usd_amount: form.usd_amount,
            },
        });
        preview.value = data;
    } catch (e) {
        previewError.value = 'No se pudo obtener el precio. Intenta de nuevo.';
    } finally {
        loadingPreview.value = false;
    }
}

function guardar() {
    form.post('/simulations/buy', {
        preserveScroll: true,
        onSuccess: () => {
            preview.value = null;
            form.reset('usd_amount');
        },
    });
}
</script>

<template>

    <Head title="Simular Compra" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Simular Compra
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">

                <!-- Mensaje de éxito -->
                <div v-if="flashSuccess"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ flashSuccess }}
                </div>

                <!-- Saldo disponible -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Saldo disponible</p>
                    <p class="text-2xl font-bold text-gray-900">{{ money(usdBalance) }}</p>
                </div>

                <!-- Formulario -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Criptomoneda</label>
                        <select v-model="form.cryptocurrency_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option v-for="c in cryptocurrencies" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.symbol }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Monto en USD</label>
                        <input v-model="form.usd_amount" type="number" min="0" step="0.01" placeholder="Ej. 1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <p v-if="form.errors.usd_amount" class="mt-1 text-sm text-red-600">
                            {{ form.errors.usd_amount }}
                        </p>
                    </div>

                    <button @click="calcular" :disabled="loadingPreview"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-50">
                        {{ loadingPreview ? 'Calculando...' : 'Calcular' }}
                    </button>

                    <p v-if="previewError" class="text-sm text-red-600">{{ previewError }}</p>
                </div>

                <!-- Resultado del preview -->
                <div v-if="preview"
                    class="overflow-hidden bg-indigo-50 border border-indigo-200 shadow-sm sm:rounded-lg p-6 space-y-2">
                    <h3 class="font-semibold text-indigo-900">Resultado de la simulación</h3>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p>Precio actual: <strong>{{ money(preview.price_usd) }}</strong> / {{ preview.symbol }}</p>
                        <p>Invertirás: <strong>{{ money(preview.usd_amount) }}</strong></p>
                        <p>Recibirás: <strong>{{ Number(preview.quantity).toFixed(8) }} {{ preview.symbol }}</strong>
                        </p>
                    </div>

                    <button @click="guardar" :disabled="form.processing"
                        class="mt-2 inline-block rounded-md bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-green-700 disabled:opacity-50">

                        {{ form.processing ? 'Guardando...' : 'Guardar simulación' }}
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
