<script setup lang="ts">
import type { RecentActivity } from '@/stores/useAdminDashboardStore'

defineProps<{
    activity: RecentActivity
}>()

const typeIcons: Record<string, string> = {
    registration: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>',
    school: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
    system: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
}

const typeColors: Record<string, string> = {
    registration: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30',
    school: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30',
    system: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30',
}
</script>

<template>
    <div class="flex items-start gap-3.5 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <div
            class="flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold text-white shrink-0"
            :style="{ background: activity.avatarColor }"
        >
            {{ activity.initials }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-snug">
                <strong class="font-semibold text-gray-900 dark:text-white">{{ activity.user }}</strong>
                {{ activity.action }}
                {{ activity.target }}
            </p>
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ activity.timestamp }}</p>
        </div>
        <div
            class="flex items-center justify-center w-8 h-8 rounded-full shrink-0"
            :class="typeColors[activity.type] || typeColors.system"
            v-html="typeIcons[activity.type] || typeIcons.system"
        />
    </div>
</template>
