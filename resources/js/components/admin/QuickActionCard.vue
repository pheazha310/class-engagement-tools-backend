<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    label: string
    icon: string
    variant: 'primary' | 'success' | 'warning' | 'info'
    route: string
}>()

const emit = defineEmits<{
    click: [route: string]
}>()

const iconMap: Record<string, string> = {
    'user-plus': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
    building: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M9 3h6v18H9z"/><path d="M16 9h4v12H16z"/><path d="M4 9h4v12H4z"/></svg>',
    'book-open': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
    'bar-chart': '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>',
}

const variantConfig = computed(() => {
    const map: Record<string, { hover: string, iconBg: string, iconColor: string }> = {
        primary: { hover: 'hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20', iconBg: 'bg-blue-50 dark:bg-blue-900/30', iconColor: 'text-blue-600 dark:text-blue-400' },
        success: { hover: 'hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20', iconBg: 'bg-emerald-50 dark:bg-emerald-900/30', iconColor: 'text-emerald-600 dark:text-emerald-400' },
        warning: { hover: 'hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20', iconBg: 'bg-amber-50 dark:bg-amber-900/30', iconColor: 'text-amber-600 dark:text-amber-400' },
        info: { hover: 'hover:border-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20', iconBg: 'bg-sky-50 dark:bg-sky-900/30', iconColor: 'text-sky-600 dark:text-sky-400' },
    }
    return map[props.variant] || map.primary
})
</script>

<template>
    <button
        class="flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl border border-gray-200 bg-gray-50 cursor-pointer transition-all duration-200 hover:-translate-y-1 hover:shadow-md dark:border-gray-600 dark:bg-gray-700/50 dark:hover:shadow-black/20"
        :class="variantConfig.hover"
        @click="emit('click', route)"
    >
        <div
            class="flex items-center justify-center w-9 h-9 rounded-lg"
            :class="[variantConfig.iconBg, variantConfig.iconColor]"
            v-html="iconMap[icon] || ''"
        />
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 text-center">{{ label }}</span>
    </button>
</template>
