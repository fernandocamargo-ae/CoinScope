<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <h1 class="text-xl font-bold text-white">Bienvenido de vuelta</h1>
        <p class="mt-1 text-sm text-slate-400">Ingresa a tu portafolio virtual.</p>

        <div v-if="status" class="mt-4 rounded-lg bg-neon/10 px-3 py-2 text-sm font-medium text-neon">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="mt-6">
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                           required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                           required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-slate-400">Recordarme</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')"
                      class="text-sm text-slate-400 hover:text-neon">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full py-2.5 text-sm"
                               :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    Iniciar sesión
                </PrimaryButton>
            </div>

            <p class="mt-6 text-center text-sm text-slate-400">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="font-semibold text-neon hover:text-neon-dark">
                    Crear cuenta
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
