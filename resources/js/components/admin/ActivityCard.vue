<script setup lang="ts">
import { computed } from 'vue'
import type { RecentActivity } from '@/stores/useAdminDashboardStore'
import { UserPlus, School, Activity, Zap } from '@lucide/vue'

const props = defineProps<{
  activity: RecentActivity
}>()

const typeConfig: Record<string, { bg: string, text: string, icon: object }> = {
  registration: { bg: 'bg-blue-50', text: 'text-blue-600', icon: UserPlus },
  school: { bg: 'bg-emerald-50', text: 'text-emerald-600', icon: School },
  system: { bg: 'bg-amber-50', text: 'text-amber-600', icon: Zap },
}

const config = computed(() => typeConfig[props.activity.type] || typeConfig.system)

const statusLabel = computed(() => {
  const map: Record<string, string> = {
    registered: 'Registered',
    created: 'Created',
    started: 'Started',
    submitted: 'Submitted',
  }
  return map[props.activity.action] || props.activity.action
})
</script>

<template>
  <div class="flex items-start gap-3.5 px-5 py-3.5 transition-colors hover:bg-gray-50">
    <div
      class="flex items-center justify-center w-9 h-9 rounded-full text-sm font-semibold text-white shrink-0"
      :style="{ background: activity.avatarColor }"
    >
      {{ activity.initials }}
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm text-gray-600 leading-snug">
        <span class="font-semibold text-gray-900">{{ activity.user }}</span>
        <span class="text-gray-500"> {{ activity.action }} </span>
        <span class="font-medium text-gray-700">{{ activity.target }}</span>
      </p>
      <p class="mt-0.5 text-xs text-gray-400">{{ activity.timestamp }}</p>
    </div>
    <span
      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium shrink-0"
      :class="[config.bg, config.text]"
    >
      <component :is="config.icon" class="w-3 h-3" />
      {{ statusLabel }}
    </span>
  </div>
</template>
