<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-edge bg-surface-2 text-slate-100 placeholder-slate-500 shadow-sm focus:border-neon focus:ring-neon"
        v-model="model"
        ref="input"
    />
</template>
