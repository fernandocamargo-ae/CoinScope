<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-night px-4 py-10">
        <div class="pointer-events-none absolute inset-0 bg-grid-faint [background-size:42px_42px] opacity-50"></div>
        <div class="pointer-events-none absolute -top-32 left-1/2 h-[420px] w-[680px] -translate-x-1/2 rounded-full bg-neon/20 blur-[150px]"></div>

        <Link href="/" class="relative z-10 mb-6 flex items-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-lg bg-neon/15 text-neon ring-1 ring-neon/30">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l9 5v10l-9 5-9-5V7z" opacity=".9"/></svg>
            </span>
            <span class="text-xl font-bold tracking-tight text-white">CoinScope</span>
        </Link>

        <div class="relative z-10 w-full overflow-hidden rounded-2xl border border-edge bg-surface/80 px-6 py-7 shadow-card backdrop-blur sm:max-w-md">
            <slot />
        </div>
    </div>
</template>
