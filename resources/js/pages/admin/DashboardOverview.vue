<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminDashboardStore } from '@/stores/useAdminDashboardStore'
import StatCard from '@/components/admin/StatCard.vue'
import ActivityCard from '@/components/admin/ActivityCard.vue'
import QuickActionCard from '@/components/admin/QuickActionCard.vue'
import { useAppearance } from '@/composables/useAppearance'

const store = useAdminDashboardStore()
const router = useRouter()
const { resolvedAppearance } = useAppearance()

const currentDate = ref('')
const currentTime = ref('')
const welcomeMessage = computed(() => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Good morning'
    if (hour < 17) return 'Good afternoon'
    return 'Good evening'
})

let dateTimeInterval: ReturnType<typeof setInterval> | null = null

function updateDateTime() {
    const now = new Date()
    currentDate.value = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
    currentTime.value = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    })
}

// Chart.js
const userChartCanvas = ref<HTMLCanvasElement | null>(null)
const activityChartCanvas = ref<HTMLCanvasElement | null>(null)
let userChart: any = null
let activityChart: any = null

function getChartColors() {
    const isDark = resolvedAppearance.value === 'dark'
    return {
        text: isDark ? '#94a3b8' : '#64748b',
        grid: isDark ? '#1e293b' : '#e2e8f0',
        tooltipBg: isDark ? '#1e293b' : '#ffffff',
        tooltipBorder: isDark ? '#334155' : '#e2e8f0',
    }
}

async function initCharts() {
    const { Chart, registerables } = await import('chart.js')
    Chart.register(...registerables)

    const colors = getChartColors()

    if (userChart) userChart.destroy()
    if (activityChart) activityChart.destroy()

    if (userChartCanvas.value) {
        userChart = new Chart(userChartCanvas.value, {
            type: 'line',
            data: JSON.parse(JSON.stringify(store.userRegistrationData)),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: colors.text, font: { size: 12 }, usePointStyle: true, padding: 16 },
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: resolvedAppearance.value === 'dark' ? '#f1f5f9' : '#0f172a',
                        bodyColor: colors.text,
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { size: 11 } },
                    },
                    y: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { size: 11 } },
                        beginAtZero: true,
                    },
                },
            },
        })
    }

    if (activityChartCanvas.value) {
        activityChart = new Chart(activityChartCanvas.value, {
            type: 'line',
            data: JSON.parse(JSON.stringify(store.platformActivityData)),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: colors.text, font: { size: 12 }, usePointStyle: true, padding: 16 },
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: resolvedAppearance.value === 'dark' ? '#f1f5f9' : '#0f172a',
                        bodyColor: colors.text,
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { size: 11 } },
                    },
                    y: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { size: 11 } },
                        beginAtZero: true,
                    },
                },
            },
        })
    }
}

function handleQuickAction(route: string) {
    router.push(route)
}

onMounted(() => {
    updateDateTime()
    dateTimeInterval = setInterval(updateDateTime, 60000)
    store.fetchDashboardData().then(() => {
        initCharts()
    })
})

onUnmounted(() => {
    if (dateTimeInterval) {
        clearInterval(dateTimeInterval)
    }
})

watch(resolvedAppearance, () => {
    setTimeout(() => initCharts(), 100)
})

function dismissError() {
    store.clearError()
}
</script>

<template>
    <div>
        <!-- Error Banner -->
        <div v-if="store.error" class="flex items-center gap-2.5 px-4 py-3 mb-6 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm font-medium animate-slideDown dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Could not load live data. Showing cached data instead.</span>
            <button class="ml-auto w-6 h-6 flex items-center justify-center rounded text-red-500 opacity-60 hover:opacity-100 hover:bg-red-100 dark:hover:bg-red-900/30 transition-all" @click="dismissError">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- Loading Overlay -->
        <div v-if="store.isLoading" class="flex items-center gap-2.5 py-2 mb-4 text-sm text-gray-400 dark:text-gray-500">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin dark:border-gray-700 dark:border-t-blue-400"></div>
            <span>Loading dashboard data...</span>
        </div>

        <!-- Dashboard Header -->
        <div class="flex flex-col gap-4 mb-7 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ welcomeMessage }}, {{ store.currentUser.name }}! Here&rsquo;s what&rsquo;s happening today.</p>
            </div>
            <div class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 shrink-0">
                <div class="text-sm font-semibold text-gray-800 dark:text-white">{{ currentDate }}</div>
                <div class="text-xs text-gray-400">{{ currentTime }}</div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
            <StatCard v-for="card in store.statsCards" :key="card.id" :card="card" />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">User Registration Overview</h3>
                    <div class="flex gap-1">
                        <button class="px-2.5 py-1 text-xs font-medium text-white bg-blue-600 rounded-md">Yearly</button>
                        <button class="px-2.5 py-1 text-xs font-medium text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Monthly</button>
                        <button class="px-2.5 py-1 text-xs font-medium text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Weekly</button>
                    </div>
                </div>
                <div class="p-5 min-h-[280px] flex items-center justify-center">
                    <canvas ref="userChartCanvas" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Platform Activity Overview</h3>
                    <div class="flex gap-1">
                        <button class="px-2.5 py-1 text-xs font-medium text-white bg-blue-600 rounded-md">Weekly</button>
                        <button class="px-2.5 py-1 text-xs font-medium text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Monthly</button>
                    </div>
                </div>
                <div class="p-5 min-h-[280px] flex items-center justify-center">
                    <canvas ref="activityChartCanvas" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Grid: Recent Activity + Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Recent Activity (spans 2 cols) -->
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
                    <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">View all</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <ActivityCard
                        v-for="activity in store.recentActivities"
                        :key="activity.id"
                        :activity="activity"
                    />
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Quick Actions</h3>
                </div>
                <div class="grid grid-cols-2 gap-3 p-5">
                    <QuickActionCard
                        v-for="action in store.quickActions"
                        :key="action.id"
                        :label="action.label"
                        :icon="action.icon"
                        :variant="action.variant"
                        :route="action.route"
                        @click="handleQuickAction"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
