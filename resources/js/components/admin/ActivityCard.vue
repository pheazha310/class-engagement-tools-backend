<script setup lang="ts">
import type { RecentActivity } from '@/stores/useAdminDashboardStore'

defineProps<{
    activity: RecentActivity
}>()

// Activity type icons
const typeIcons: Record<string, string> = {
    registration: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>',
    school: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
    system: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
}

const typeColors: Record<string, string> = {
    registration: 'var(--color-primary)',
    school: 'var(--color-success)',
    system: 'var(--color-warning)',
}

const typeBgs: Record<string, string> = {
    registration: 'var(--color-primary-light)',
    school: 'var(--color-success-light)',
    system: 'var(--color-warning-light)',
}
</script>

<template>
    <div class="activity-item">
        <div class="activity-item-avatar" :style="{ background: activity.avatarColor }">
            {{ activity.initials }}
        </div>
        <div class="activity-item-content">
            <p class="activity-desc">
                <strong>{{ activity.user }}</strong>
                {{ activity.action }}
                {{ activity.target }}
            </p>
            <p class="activity-time">{{ activity.timestamp }}</p>
        </div>
        <div class="activity-icon" :style="{ background: typeBgs[activity.type] || typeBgs.system, color: typeColors[activity.type] || typeColors.system }" v-html="typeIcons[activity.type] || typeIcons.system" />
    </div>
</template>
