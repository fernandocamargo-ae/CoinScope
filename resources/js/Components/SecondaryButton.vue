<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-edge bg-surface-2 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-200 shadow-sm transition duration-150 ease-in-out hover:border-slate-600 focus:outline-none disabled:opacity-25"
    >
        <slot />
    </button>
</template>
