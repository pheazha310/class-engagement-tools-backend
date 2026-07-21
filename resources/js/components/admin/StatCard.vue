<script setup lang="ts">
import { computed } from 'vue'
import type { StatCard } from '@/stores/useAdminDashboardStore'
import { Users, GraduationCap, BookOpen, Activity, Clock, UserX, Building2 } from '@lucide/vue'

const props = defineProps<{
  card: StatCard
}>()

const icons: Record<string, object> = {
  users: Users,
  school: GraduationCap,
  book: BookOpen,
  activity: Activity,
  clock: Clock,
  userX: UserX,
  building2: Building2,
}

const accentConfig = computed(() => {
  const map: Record<string, { circle: string, circleBg: string, text: string }> = {
    primary: { circle: 'text-blue-600', circleBg: 'bg-blue-50', text: 'text-blue-600' },
    success: { circle: 'text-emerald-600', circleBg: 'bg-emerald-50', text: 'text-emerald-600' },
    warning: { circle: 'text-amber-600', circleBg: 'bg-amber-50', text: 'text-amber-600' },
    info: { circle: 'text-sky-600', circleBg: 'bg-sky-50', text: 'text-sky-600' },
  }
  return map[props.card.accent] || map.primary
})

const isPositive = computed(() => props.card.growth >= 0)
</script>

<template>
  <div
    class="bg-white rounded-2xl shadow-sm p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-default"
  >
    <div class="flex items-start justify-between">
      <div class="space-y-3">
        <p class="text-sm font-medium text-gray-500">{{ card.title }}</p>
        <p class="text-3xl font-bold text-gray-900 leading-none tracking-tight">{{ card.value.toLocaleString() }}</p>
        <div class="flex items-center gap-1.5">
          <span
            class="inline-flex items-center gap-1 text-xs font-semibold"
            :class="isPositive ? 'text-emerald-600' : 'text-red-600'"
          >
            <svg
              v-if="isPositive"
              xmlns="http://www.w3.org/2000/svg"
              class="w-3 h-3"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="18 15 12 9 6 15" />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="w-3 h-3"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="6 9 12 15 18 9" />
            </svg>
            {{ isPositive ? '+' : '' }}{{ card.growth.toFixed(1) }}%
          </span>
          <span class="text-xs text-gray-400">{{ card.growthLabel }}</span>
        </div>
      </div>
      <div
        class="flex items-center justify-center w-12 h-12 rounded-full shrink-0"
        :class="accentConfig.circleBg"
      >
        <component
          :is="icons[card.icon] || Activity"
          class="w-5 h-5"
          :class="accentConfig.circle"
        />
      </div>
    </div>
  </div>
</template>
