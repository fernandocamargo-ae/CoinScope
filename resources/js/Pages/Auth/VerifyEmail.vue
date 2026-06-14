<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificación de correo" />

        <p class="mb-4 text-sm text-slate-400">
            ¡Gracias por registrarte! Verifica tu correo haciendo clic en el enlace
            que te enviamos. Si no lo recibiste, te enviamos otro con gusto.
        </p>

        <div
            class="mb-4 rounded-lg bg-neon/10 px-3 py-2 text-sm font-medium text-neon"
            v-if="verificationLinkSent"
        >
            Se envió un nuevo enlace de verificación a tu correo.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reenviar correo
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-slate-400 underline hover:text-neon focus:outline-none"
                    >Cerrar sesión</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
