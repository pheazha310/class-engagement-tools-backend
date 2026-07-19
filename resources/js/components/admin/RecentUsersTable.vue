<script setup lang="ts">
import type { RecentActivity } from '@/stores/useAdminDashboardStore'

const props = defineProps<{
    users: RecentActivity[]
}>()

function getInitials(name: string): string {
    return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
}

const avatarColors = [
    'bg-blue-500', 'bg-emerald-500', 'bg-violet-500', 'bg-amber-500',
    'bg-rose-500', 'bg-cyan-500', 'bg-pink-500', 'bg-indigo-500',
]

function getAvatarColor(name: string): string {
    let hash = 0
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
    return avatarColors[Math.abs(hash) % avatarColors.length]
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr
                    v-for="(user, index) in users.slice(0, 5)"
                    :key="user.id"
                    class="transition-colors duration-150 hover:bg-gray-50/80 dark:hover:bg-gray-700/40"
                >
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold text-white shadow-sm"
                                :class="getAvatarColor(user.user)"
                            >
                                {{ getInitials(user.user) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ user.user }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ user.target }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium" :class="[
                            user.action === 'registered'
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                : user.action === 'created'
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                    : 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                        ]">
                            {{ user.action === 'registered' ? 'User' : user.action === 'created' ? 'Admin' : 'System' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :class="user.action === 'registered' ? 'bg-emerald-500' : 'bg-blue-500'" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ user.action === 'registered' ? 'Active' : 'Completed' }}</span>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ user.timestamp }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
