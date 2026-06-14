<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <h1 class="text-xl font-bold text-white">Crea tu cuenta</h1>
        <p class="mt-1 text-sm text-slate-400">Empiezas con $100,000 USD virtuales para practicar.</p>

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
