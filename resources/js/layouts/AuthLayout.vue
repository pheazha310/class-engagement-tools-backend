<script setup lang="ts">
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthCardLayout :title="title" :description="description">
        <slot />
    </AuthCardLayout>
</template>
