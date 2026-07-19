<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminDashboardStore } from '@/stores/useAdminDashboardStore'
import StatCard from '@/components/admin/StatCard.vue'
import ActivityCard from '@/components/admin/ActivityCard.vue'
import QuickActionCard from '@/components/admin/QuickActionCard.vue'
import { useAppearance } from '@/composables/useAppearance'
import { ArrowRight, X } from '@lucide/vue'

const store = useAdminDashboardStore()
const router = useRouter()
const { resolvedAppearance } = useAppearance()

const welcomeMessage = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 17) return 'Good afternoon'
  return 'Good evening'
})

const userChartCanvas = ref<HTMLCanvasElement | null>(null)
const pieChartCanvas = ref<HTMLCanvasElement | null>(null)
let userChart: any = null
let pieChart: any = null

function getChartColors() {
  const isDark = resolvedAppearance.value === 'dark'
  return {
    text: isDark ? '#94a3b8' : '#64748b',
    grid: isDark ? '#1e293b' : '#f1f5f9',
    tooltipBg: isDark ? '#1e293b' : '#ffffff',
    tooltipBorder: isDark ? '#334155' : '#e2e8f0',
    tooltipTitle: isDark ? '#f1f5f9' : '#0f172a',
  }
}

async function initCharts() {
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)

  const colors = getChartColors()

  if (userChart) userChart.destroy()
  if (pieChart) pieChart.destroy()

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
            labels: { color: colors.text, font: { size: 11, weight: '500' as any }, usePointStyle: true, padding: 16, pointStyleWidth: 8 },
          },
          tooltip: {
            backgroundColor: colors.tooltipBg,
            titleColor: colors.tooltipTitle,
            bodyColor: colors.text,
            borderColor: colors.tooltipBorder,
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
          },
        },
        scales: {
          x: { grid: { display: false }, ticks: { color: colors.text, font: { size: 11 } } },
          y: { grid: { color: colors.grid } as any, ticks: { color: colors.text, font: { size: 11 } }, beginAtZero: true },
        },
        elements: {
          line: { tension: 0.4, borderWidth: 3 },
          point: { radius: 0, hoverRadius: 5, hoverBorderWidth: 3 },
        },
      },
    })
  }

  if (pieChartCanvas.value) {
    pieChart = new Chart(pieChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: ['Students', 'Teachers', 'Admins'],
        datasets: [{
          data: doughnutData,
          backgroundColor: ['#2563EB', '#10B981', '#F59E0B'],
          borderColor: resolvedAppearance.value === 'dark' ? '#1e293b' : '#ffffff',
          borderWidth: 3,
          hoverOffset: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: colors.text, font: { size: 11, weight: '500' as any }, usePointStyle: true, padding: 14, pointStyle: 'circle', pointStyleWidth: 8 },
          },
          tooltip: {
            backgroundColor: colors.tooltipBg,
            titleColor: colors.tooltipTitle,
            bodyColor: colors.text,
            borderColor: colors.tooltipBorder,
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: (ctx: any) => {
                const data = ctx.dataset.data as number[]
                const total = data.reduce((a: number, b: number) => a + b, 0)
                const pct = ((ctx.parsed / total) * 100).toFixed(1)
                return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`
              },
            },
          },
        },
      },
    })
  }
}

onMounted(async () => {
  if (!store.hydratedFromServer) {
    await store.fetchDashboardData()
  }
  setTimeout(() => initCharts(), 100)
})

onUnmounted(() => {
  if (userChart) userChart.destroy()
  if (pieChart) pieChart.destroy()
})

watch(resolvedAppearance, () => {
  setTimeout(() => initCharts(), 100)
})

function handleQuickAction(route: string) {
  router.push(route)
}

const doughnutData = [1843, 520, 80]
const totalUsers = computed(() => doughnutData.reduce((a: number, b: number) => a + b, 0))

function dismissError() { store.clearError() }
</script>

<template>
  <div class="space-y-6">
    <div
      v-if="store.error"
      class="flex items-center gap-3 px-5 py-3.5 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-medium animate-slideDown"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span class="flex-1">{{ store.error }}</span>
      <button class="w-6 h-6 flex items-center justify-center rounded text-red-400 hover:text-red-600 hover:bg-red-100 transition-all" @click="dismissError">
        <X class="size-4" />
      </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ welcomeMessage }}, {{ store.currentUser.name.split(' ')[0] }}</h1>
        <p class="mt-1 text-sm text-gray-500">Here&rsquo;s what&rsquo;s happening with your platform today.</p>
      </div>
      <div class="flex items-center gap-2">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <StatCard v-for="card in store.statsCards.slice(0, 4)" :key="card.id" :card="card" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div>
            <h3 class="text-sm font-semibold text-gray-900">User Growth</h3>
            <p class="text-xs text-gray-400 mt-0.5">Monthly registration trends</p>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-400">
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600" />
              Students
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" />
              Teachers
            </span>
          </div>
        </div>
        <div class="p-6 h-72">
          <canvas ref="userChartCanvas" />
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-900">User Distribution</h3>
          <p class="text-xs text-gray-400 mt-0.5">By role</p>
        </div>
        <div class="p-6 h-72 flex items-center justify-center">
          <div class="relative w-full h-full">
            <canvas ref="pieChartCanvas" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ totalUsers.toLocaleString() }}</div>
                <div class="text-[10px] text-gray-400 -mt-0.5">Total Users</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-900">Recent Activity</h3>
          <router-link
            to="/admin/dashboard/activity"
            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors"
          >
            View All
            <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
        </div>
        <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-100">
          <ActivityCard
            v-for="activity in store.recentActivities.slice(0, 5)"
            :key="activity.id"
            :activity="activity"
          />
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
          <button class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
            Mark all read
          </button>
        </div>
        <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-100 overflow-hidden">
          <div
            v-for="notif in store.notifications"
            :key="notif.id"
            class="flex items-start gap-3.5 px-5 py-3.5 hover:bg-gray-50 transition-colors cursor-pointer"
          >
            <div class="flex items-center justify-center w-9 h-9 rounded-full shrink-0" :style="{ background: notif.iconBg }">
              <span class="text-base">{{ notif.icon }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-600 leading-snug" v-html="notif.text" />
              <div class="flex items-center gap-2 mt-1">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600" />
                <span class="text-xs text-gray-400">{{ notif.time }}</span>
              </div>
            </div>
          </div>
          <div class="px-5 py-3 text-center">
            <button class="text-xs font-medium text-gray-400 hover:text-gray-600 transition-colors">
              View all notifications
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slideDown {
    animation: slideDown 0.3s ease-out;
}
</style>
