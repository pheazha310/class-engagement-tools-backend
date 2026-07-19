<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useAppearance } from '@/composables/useAppearance'
import { Button } from '@/components/ui/button'

const { resolvedAppearance } = useAppearance()

// ─── Date filters ─────────────────────────────────────
const dateRange = ref('30d')
const dateRanges = [
    { value: '7d', label: '7 Days' },
    { value: '30d', label: '30 Days' },
    { value: '90d', label: '90 Days' },
    { value: '1y', label: '1 Year' },
]

// ─── KPI Data ─────────────────────────────────────────
const kpiCards = ref([
    { label: 'Total Revenue', value: '$12,480', change: '+18.2%', trend: 'up', icon: 'dollar' },
    { label: 'Active Users', value: '1,284', change: '+12.5%', trend: 'up', icon: 'users' },
    { label: 'Quiz Completion', value: '89.3%', change: '+5.7%', trend: 'up', icon: 'check' },
    { label: 'Avg. Session', value: '24m 36s', change: '-2.1%', trend: 'down', icon: 'clock' },
    { label: 'Bounce Rate', value: '32.1%', change: '-4.3%', trend: 'up', icon: 'trending' },
    { label: 'New Signups', value: '342', change: '+22.8%', trend: 'up', icon: 'user-plus' },
])

// ─── Summary Stats ────────────────────────────────────
const summaryStats = ref([
    { label: 'Total Users', value: '2,843', sub: '+184 this month' },
    { label: 'Total Schools', value: '48', sub: '+3 this month' },
    { label: 'Total Quizzes Created', value: '156', sub: '+22 this month' },
    { label: 'Total Submissions', value: '12,847', sub: '+1,274 this month' },
    { label: 'Teachers', value: '86', sub: '+8 this month' },
    { label: 'Students', value: '2,757', sub: '+176 this month' },
])

// ─── Chart refs ────────────────────────────────────────
const revenueChartCanvas = ref<HTMLCanvasElement | null>(null)
const pieChartCanvas = ref<HTMLCanvasElement | null>(null)
const barChartCanvas = ref<HTMLCanvasElement | null>(null)
let revenueChart: any = null
let pieChart: any = null
let barChart: any = null

function getChartColors() {
    const isDark = resolvedAppearance.value === 'dark'
    return {
        text: isDark ? '#94a3b8' : '#64748b',
        grid: isDark ? '#1e293b' : '#f1f5f9',
        tooltipBg: isDark ? '#1e293b' : '#ffffff',
        tooltipBorder: isDark ? '#334155' : '#e2e8f0',
    }
}

async function initCharts() {
    const { Chart, registerables } = await import('chart.js')
    Chart.register(...registerables)

    const colors = getChartColors()

    if (revenueChart) revenueChart.destroy()
    if (pieChart) pieChart.destroy()
    if (barChart) barChart.destroy()

    // Revenue/Activity Bar Chart
    if (revenueChartCanvas.value) {
        revenueChart = new Chart(revenueChartCanvas.value, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Engagements',
                        data: [650, 720, 580, 840, 920, 780, 690, 750, 880, 910, 860, 790],
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        borderColor: '#4f46e5',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.5,
                    },
                    {
                        label: 'New Users',
                        data: [320, 450, 380, 520, 610, 480, 390, 420, 560, 490, 530, 470],
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.5,
                    },
                ],
            },
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

    // Pie Chart - Content Distribution
    if (pieChartCanvas.value) {
        pieChart = new Chart(pieChartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Quizzes', 'Polls', 'Wheel Spins', 'Other'],
                datasets: [{
                    data: [45, 30, 18, 7],
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#94a3b8'],
                    borderColor: resolvedAppearance.value === 'dark' ? '#1e293b' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: colors.text, font: { size: 11 }, usePointStyle: true, padding: 14 },
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: resolvedAppearance.value === 'dark' ? '#f1f5f9' : '#0f172a',
                        bodyColor: colors.text,
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx: any) => {
                                const total = ctx.dataset.data.reduce((a: number, b: number) => a + b, 0)
                                return ` ${ctx.label}: ${((ctx.parsed / total) * 100).toFixed(1)}%`
                            },
                        },
                    },
                },
            },
        })
    }

    // School Performance Bar Chart
    if (barChartCanvas.value) {
        barChart = new Chart(barChartCanvas.value, {
            type: 'bar',
            data: {
                labels: ['School A', 'School B', 'School C', 'School D', 'School E'],
                datasets: [{
                    label: 'Active Users',
                    data: [420, 380, 290, 210, 150],
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    borderColor: ['#4f46e5', '#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: resolvedAppearance.value === 'dark' ? '#f1f5f9' : '#0f172a',
                        bodyColor: colors.text,
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { size: 11 } },
                        beginAtZero: true,
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: colors.text, font: { size: 11 } },
                    },
                },
            },
        })
    }
}

function exportData(type: 'pdf' | 'excel') {
    // Placeholder for export functionality
    alert(`Export as ${type.toUpperCase()} triggered. This would download the report.`)
}

onMounted(() => {
    setTimeout(() => initCharts(), 100)
})

watch(resolvedAppearance, () => {
    setTimeout(() => initCharts(), 100)
})
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Reports & Analytics</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comprehensive analytics and performance metrics</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" class="gap-2 cursor-pointer" @click="exportData('pdf')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Export PDF
                </Button>
                <Button variant="outline" size="sm" class="gap-2 cursor-pointer" @click="exportData('excel')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="16" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Export Excel
                </Button>
            </div>
        </div>

        <!-- Date Filters -->
        <div class="flex items-center gap-2 mb-6">
            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Period:</span>
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-0.5 rounded-lg">
                <button
                    v-for="range in dateRanges"
                    :key="range.value"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all cursor-pointer"
                    :class="dateRange === range.value
                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    @click="dateRange = range.value"
                >
                    {{ range.label }}
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 mb-7">
            <div
                v-for="kpi in kpiCards"
                :key="kpi.label"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-shadow"
            >
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ kpi.label }}</p>
                <div class="flex items-end justify-between">
                    <span class="text-xl font-bold text-gray-900 dark:text-white">{{ kpi.value }}</span>
                    <span
                        class="text-xs font-semibold"
                        :class="kpi.trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'"
                    >
                        {{ kpi.change }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
            <!-- Bar Chart - Engagement -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Platform Engagement</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Monthly engagement and new user metrics</p>
                </div>
                <div class="p-5 min-h-[280px]">
                    <canvas ref="revenueChartCanvas" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>

            <!-- Pie Chart - Content Distribution -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Content Distribution</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Breakdown of content types</p>
                </div>
                <div class="p-5 min-h-[280px] flex items-center justify-center">
                    <canvas ref="pieChartCanvas" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>
        </div>

        <!-- School Performance Bar Chart -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden mb-7">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">School Performance</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Active users per educational institution</p>
            </div>
            <div class="p-5 min-h-[260px]">
                <canvas ref="barChartCanvas" style="width: 100%; height: 240px;"></canvas>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Summary Statistics</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Key platform metrics at a glance</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-px bg-gray-100 dark:bg-gray-700">
                <div
                    v-for="stat in summaryStats"
                    :key="stat.label"
                    class="bg-white dark:bg-gray-800 p-4"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ stat.label }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ stat.sub }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
