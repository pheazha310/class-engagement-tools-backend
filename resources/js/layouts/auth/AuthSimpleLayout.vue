<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-white p-6"
    >
        <div class="w-full max-w-sm">
            <slot />
        </div>
    </div>
</template>
