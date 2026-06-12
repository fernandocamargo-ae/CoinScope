<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    holdings: Array,
    usdBalance: Number,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const preview = ref(null);
const previewError = ref(null);
const loadingPreview = ref(false);

const form = useForm({
    cryptocurrency_id: props.holdings[0]?.cryptocurrency_id ?? null,
    quantity: '',
});

const selected = computed(() =>
    props.holdings.find(h => h.cryptocurrency_id === form.cryptocurrency_id)
);

const usd = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);
const gtq = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(n ?? 0);

function venderTodo() {
    if (selected.value) form.quantity = selected.value.balance;
}

async function calcular() {
    previewError.value = null;
    preview.value = null;

    if (!form.cryptocurrency_id || !form.quantity) {
        previewError.value = 'Selecciona una cripto e ingresa una cantidad.';
        return;
    }

    loadingPreview.value = true;
    try {
        const { data } = await window.axios.get('/simulations/sell/preview', {
            params: {
                cryptocurrency_id: form.cryptocurrency_id,
                quantity: form.quantity,
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
    form.post('/simulations/sell', {
        preserveScroll: true,
        onSuccess: () => {
            preview.value = null;
            form.reset('quantity');
        },
    });
}
</script>

<template>

    <Head title="Simular Venta" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Simular Venta</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">

                <div v-if="flashSuccess"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ flashSuccess }}
                </div>

                <!-- Sin criptos -->
                <div v-if="holdings.length === 0" class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                    No tienes criptomonedas para vender todavía.
                    <a href="/simulations/buy" class="text-indigo-600 underline">Simula una compra primero</a>.
                </div>

                <template v-else>
                    <!-- Formulario -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Criptomoneda</label>
                            <select v-model="form.cryptocurrency_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="h in holdings" :key="h.cryptocurrency_id" :value="h.cryptocurrency_id">
                                    {{ h.name }} ({{ h.symbol }})
                                </option>
                            </select>
                            <p v-if="selected" class="mt-1 text-xs text-gray-500">
                                Disponible: {{ Number(selected.balance).toFixed(8) }} {{ selected.symbol }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cantidad a vender</label>
                            <div class="mt-1 flex gap-2">
                                <input v-model="form.quantity" type="number" min="0" step="0.00000001"
                                    placeholder="Ej. 0.01"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <button type="button" @click="venderTodo"
                                    class="whitespace-nowrap rounded-md bg-gray-100 px-3 text-sm text-gray-700 hover:bg-gray-200">
                                    Vender todo
                                </button>
                            </div>
                            <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}
                            </p>
                        </div>

                        <button @click="calcular" :disabled="loadingPreview"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-50">
                            {{ loadingPreview ? 'Calculando...' : 'Calcular' }}
                        </button>

                        <p v-if="previewError" class="text-sm text-red-600">{{ previewError }}</p>
                    </div>

                    <!-- Resultado -->
                    <div v-if="preview"
                        class="bg-indigo-50 border border-indigo-200 shadow-sm sm:rounded-lg p-6 space-y-2">
                        <h3 class="font-semibold text-indigo-900">Resultado de la venta</h3>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p>Precio actual: <strong>{{ usd(preview.price_usd) }}</strong> / {{ preview.symbol }}</p>
                            <p>Venderás: <strong>{{ Number(preview.quantity).toFixed(8) }} {{ preview.symbol }}</strong>
                            </p>
                            <p>Recibirás (USD): <strong>{{ usd(preview.usd_value) }}</strong></p>
                            <p>Equivalente (GTQ): <strong>{{ gtq(preview.gtq_value) }}</strong></p>
                        </div>

                        <button @click="guardar" :disabled="form.processing"
                            class="mt-2 inline-block rounded-md bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-green-700 disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : 'Guardar simulación' }}
                        </button>
                    </div>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
