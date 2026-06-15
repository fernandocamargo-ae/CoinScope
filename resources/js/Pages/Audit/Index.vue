<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    logs: Object,
});

const badge = (a) => {
    if (a?.startsWith('SIMULATE')) return 'bg-ice/15 text-ice';
    if (a === 'LOGIN' || a === 'REGISTER') return 'bg-neon/15 text-neon';
    if (a === 'LOGOUT') return 'bg-surface-2 text-slate-300';
    return 'bg-surface-2 text-slate-300';
};
</script>

<template>
    <Head title="Auditoría" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Auditoría de Actividades</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-4">

                <p class="text-sm text-slate-400">
                    Registro de los eventos importantes de tu cuenta (trazabilidad). RF-010.
                </p>

                <div class="overflow-x-auto rounded-2xl border border-edge bg-surface shadow-card">
                    <table class="w-full min-w-[600px] divide-y divide-edge text-sm">
                        <thead class="bg-surface-2/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Fecha y hora</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Acción</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">Descripción</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-400">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-edge/60">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-surface-2/40">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400">{{ log.created_at }}</td>
                                <td class="px-4 py-3">
                                    <span :class="badge(log.action)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ log.description }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ log.ip_address }}</td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                    No hay eventos registrados todavía.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="logs.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="(link, i) in logs.links" :key="i">
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
