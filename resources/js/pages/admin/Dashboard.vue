<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import adminRouter from '@/router/admin'

const props = defineProps<{
    stats?: any
    activities?: any
    user?: any
}>()

onMounted(() => {
    // Mount the Vue Router on this page
    const app = document.getElementById('admin-app')
    if (app) {
        // The router is already mounted via app.ts
        // Navigate to the dashboard overview
        adminRouter.push('/admin/dashboard')
    }
})
</script>

<template>
    <Head title="Admin Dashboard" />
    <div id="admin-app" class="admin-root">
        <router-view />
    </div>
</template>
