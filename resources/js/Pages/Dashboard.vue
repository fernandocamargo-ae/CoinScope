<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    usdBalance: { type: Number, default: 0 },
    holdings: { type: Array, default: () => [] },
    cryptoValue: { type: Number, default: 0 },
    totalValue: { type: Number, default: 0 },
});

const usd = (n) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n ?? 0);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Mi Portafolio</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

                <!-- Tarjetas resumen -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Saldo en efectivo (USD)</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ usd(usdBalance) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Valor en criptomonedas</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ usd(cryptoValue) }}</p>
                    </div>
                    <div class="rounded-lg bg-indigo-600 p-6 shadow-sm">
                        <p class="text-sm text-indigo-100">Valor total del portafolio</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ usd(totalValue) }}</p>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="flex flex-wrap gap-3">
                    <Link :href="route('simulations.buy.form')"
                          class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-700">
                        Comprar
                    </Link>
                    <Link :href="route('simulations.sell.form')"
                          class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700">
                        Vender
                    </Link>
                    <Link :href="route('simulations.history')"
                          class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-700">
                        Ver historial
                    </Link>
                </div>

                <!-- Tabla de tenencias -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Mis criptomonedas</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Activo</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Cantidad</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Precio actual</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Valor (USD)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="h in holdings" :key="h.symbol">
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-900">{{ h.symbol }}</span>
                                    <span class="ml-1 text-gray-400">{{ h.name }}</span>
                                </td>
                                <td class="px-6 py-3 text-right text-gray-700">
                                    {{ Number(h.balance).toFixed(8) }}
                                </td>
                                <td class="px-6 py-3 text-right text-gray-700">{{ usd(h.price_usd) }}</td>
                                <td class="px-6 py-3 text-right font-medium text-gray-900">{{ usd(h.value_usd) }}</td>
                            </tr>
                            <tr v-if="holdings.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    Aún no tienes criptomonedas.
                                    <Link :href="route('simulations.buy.form')" class="text-indigo-600 underline">
                                        Simula tu primera compra
                                    </Link>.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
