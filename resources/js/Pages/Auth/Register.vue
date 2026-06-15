<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    cryptocurrencies: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    initial_usd: 100000,
    holdings: props.cryptocurrencies.map((c) => ({
        cryptocurrency_id: c.id,
        symbol: c.symbol,
        name: c.name,
        quantity: '',
    })),
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        initial_usd: data.initial_usd === '' || data.initial_usd == null ? 100000 : data.initial_usd,
        holdings: data.holdings.map((h) => ({
            cryptocurrency_id: h.cryptocurrency_id,
            quantity: h.quantity === '' || h.quantity == null ? 0 : h.quantity,
        })),
    })).post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <h1 class="text-xl font-bold text-white">Crea tu cuenta</h1>
        <p class="mt-1 text-sm text-slate-400">Define tu estado inicial: saldo y las criptos que ya posees.</p>

        <form @submit.prevent="submit" class="mt-6">
            <div>
                <InputLabel for="name" value="Nombre" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name"
                           required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                           required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                           required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                           v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <!-- Estado inicial -->
            <div class="mt-6 rounded-xl border border-edge bg-surface-2/40 p-4">
                <h2 class="text-sm font-semibold text-white">Estado inicial</h2>
                <p class="mt-0.5 text-xs text-slate-400">
                    Tu saldo de partida y las criptomonedas que ya posees. Se verán afectados al guardar tus simulaciones.
                </p>

                <div class="mt-3">
                    <label class="block text-xs font-medium text-slate-400">Saldo inicial en USD</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                        <input v-model="form.initial_usd" type="number" min="0" step="0.01"
                               class="block w-full rounded-lg border-edge bg-surface-2 pl-7 text-slate-100 shadow-sm focus:border-neon focus:ring-neon" />
                    </div>
                    <InputError class="mt-1" :message="form.errors.initial_usd" />
                </div>

                <div v-if="form.holdings.length" class="mt-4 space-y-2">
                    <p class="text-xs font-medium text-slate-400">Criptomonedas que ya tienes (opcional)</p>
                    <div v-for="(h, i) in form.holdings" :key="h.cryptocurrency_id" class="flex items-center gap-2">
                        <span class="w-24 shrink-0 text-sm">
                            <span class="font-semibold text-white">{{ h.symbol }}</span>
                            <span class="ml-1 text-xs text-slate-500">{{ h.name }}</span>
                        </span>
                        <input v-model="form.holdings[i].quantity" type="number" min="0" step="0.00000001"
                               placeholder="0.00000000"
                               class="block w-full rounded-lg border-edge bg-surface-2 text-sm text-slate-100 placeholder-slate-600 shadow-sm focus:border-neon focus:ring-neon" />
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full py-2.5 text-sm"
                               :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    Crear cuenta
                </PrimaryButton>
            </div>

            <p class="mt-6 text-center text-sm text-slate-400">
                ¿Ya tienes cuenta?
                <Link :href="route('login')" class="font-semibold text-neon hover:text-neon-dark">
                    Iniciar sesión
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
