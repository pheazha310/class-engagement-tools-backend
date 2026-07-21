<script setup lang="ts">
import { onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useAdminDashboardStore, type StatCard, type ChartData, type RecentActivity, type Notification, type CurrentUser } from '@/stores/useAdminDashboardStore'
import adminRouter from '@/router/admin'

/**
 * Server-side props passed by the backend via Inertia.
 * The backend controller can inject these to avoid an extra API call on initial load.
 */
export interface DashboardServerProps {
    stats?: StatCard[]
    userRegistrationData?: ChartData
    platformActivityData?: ChartData
    recentActivities?: RecentActivity[]
    notifications?: Notification[]
    currentUser?: CurrentUser
}

const props = defineProps<DashboardServerProps>()

const store = useAdminDashboardStore()

// ─── Synchronous store hydration (runs before any child mounts) ───
// Vue 3 lifecycle runs onMounted bottom-up (children first).
// So this must execute at the top level of <script setup>, NOT in onMounted,
// to ensure the store is populated before DashboardOverview mounts.
if (props.stats && props.stats.length > 0) {
    store.applyApiData({
        stats: props.stats,
        userRegistrationData: props.userRegistrationData
            ?? store.userRegistrationData,
        platformActivityData: props.platformActivityData
            ?? store.platformActivityData,
        recentActivities: props.recentActivities ?? store.recentActivities,
        notifications: props.notifications ?? store.notifications,
        currentUser: props.currentUser ?? store.currentUser,
    })
}

onMounted(() => {
    // The child component (DashboardOverview.vue) handles the async API
    // fallback fetch if store.hydratedFromServer is false.

    // Sync the Vue Router to the current Inertia URL.
    // The router uses createMemoryHistory so it doesn't read the browser URL.
    const currentPath = window.location.pathname + window.location.search
    if (adminRouter.currentRoute.value.fullPath !== currentPath) {
        adminRouter.push(currentPath)
    }
})
</script>

<template>
    <Head title="Admin Dashboard" />

    <!--
        The admin Vue Router renders AdminDashboardShell as the root component
        for /admin/dashboard, which contains sidebar, navbar, and its own
        <router-view /> for child pages (DashboardOverview, Users, Roles, etc.)
    -->
    <router-view />
</template>
