<script setup lang="ts">
import { computed } from 'vue'
import type { StatCard } from '@/stores/useAdminDashboardStore'

const props = defineProps<{
    card: StatCard
}>()

const icons: Record<string, string> = {
    users: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    school: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
    book: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>',
    activity: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
}

const accentConfig = computed(() => {
    const map: Record<string, { bar: string, iconBg: string, iconColor: string, growthBg: string, growthText: string }> = {
        primary: { bar: 'bg-blue-600', iconBg: 'bg-blue-50 dark:bg-blue-900/30', iconColor: 'text-blue-600 dark:text-blue-400', growthBg: 'bg-blue-50 dark:bg-blue-900/30', growthText: 'text-blue-600 dark:text-blue-400' },
        success: { bar: 'bg-emerald-500', iconBg: 'bg-emerald-50 dark:bg-emerald-900/30', iconColor: 'text-emerald-600 dark:text-emerald-400', growthBg: 'bg-emerald-50 dark:bg-emerald-900/30', growthText: 'text-emerald-600 dark:text-emerald-400' },
        warning: { bar: 'bg-amber-500', iconBg: 'bg-amber-50 dark:bg-amber-900/30', iconColor: 'text-amber-600 dark:text-amber-400', growthBg: 'bg-amber-50 dark:bg-amber-900/30', growthText: 'text-amber-600 dark:text-amber-400' },
        info: { bar: 'bg-sky-500', iconBg: 'bg-sky-50 dark:bg-sky-900/30', iconColor: 'text-sky-600 dark:text-sky-400', growthBg: 'bg-sky-50 dark:bg-sky-900/30', growthText: 'text-sky-600 dark:text-sky-400' },
    }
    return map[props.card.accent] || map.primary
})

const growthSymbol = computed(() => props.card.growth >= 0 ? '↑' : '↓')
const growthAbs = computed(() => Math.abs(props.card.growth).toFixed(1))
</script>

<template>
    <div class="relative bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-1 overflow-hidden dark:bg-gray-800 dark:border-gray-700 dark:hover:shadow-lg dark:hover:shadow-black/20">
        <!-- Accent bar -->
        <div class="absolute top-0 left-0 w-full h-1" :class="accentConfig.bar" />

        <div class="p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center justify-center w-11 h-11 rounded-lg" :class="[accentConfig.iconBg, accentConfig.iconColor]" v-html="icons[card.icon] || icons.activity" />
                <div class="flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-md" :class="[accentConfig.growthBg, accentConfig.growthText]">
                    {{ growthSymbol }} {{ growthAbs }}%
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ card.value.toLocaleString() }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ card.description }}</div>
        </div>
    </div>
</template>
