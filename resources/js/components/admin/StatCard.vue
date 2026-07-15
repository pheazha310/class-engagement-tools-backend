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

const growthSymbol = computed(() => props.card.growth >= 0 ? '↑' : '↓')
const growthClass = computed(() => props.card.growth >= 0 ? 'up' : 'down')
const growthAbs = computed(() => Math.abs(props.card.growth).toFixed(1))
</script>

<template>
    <div class="stat-card" :class="`accent-${card.accent}`">
        <div class="stat-card-header">
            <div class="stat-icon-wrapper" v-html="icons[card.icon] || icons.activity" />
            <div class="stat-growth" :class="growthClass">
                {{ growthSymbol }} {{ growthAbs }}%
            </div>
        </div>
        <div class="stat-value">{{ card.value.toLocaleString() }}</div>
        <div class="stat-label">{{ card.description }}</div>
    </div>
</template>
