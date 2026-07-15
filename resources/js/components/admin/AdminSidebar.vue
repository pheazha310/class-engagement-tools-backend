<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { useAppearance } from '@/composables/useAppearance'

interface SidebarItem {
    label: string
    icon: string
    route: string
    badge?: string | number
}

const props = defineProps<{
    collapsed: boolean
    mobileOpen: boolean
}>()

const emit = defineEmits<{
    'toggle-collapse': []
    'close-mobile': []
}>()

const route = useRoute()
const router = useRouter()
const mainMenuItems: SidebarItem[] = [
    { label: 'Dashboard', icon: 'grid', route: '/admin/dashboard' },
    { label: 'User Management', icon: 'users', route: '/admin/users', badge: '2.8K' },
    { label: 'School Management', icon: 'building', route: '/admin/schools', badge: '48' },
    { label: 'Classroom Management', icon: 'book', route: '/admin/classes', badge: '156' },
    { label: 'Activities', icon: 'activity', route: '/admin/activities' },
    { label: 'Reports', icon: 'chart', route: '/admin/reports' },
]

const bottomMenuItems: SidebarItem[] = [
    { label: 'Settings', icon: 'settings', route: '/admin/settings' },
    { label: 'Logout', icon: 'logout', route: '/logout' },
]

function isActive(itemRoute: string): boolean {
    return route.path === itemRoute || route.path.startsWith(itemRoute + '/')
}

function navigateTo(item: SidebarItem) {
    if (item.route === '/logout') {
        window.location.href = '/logout'
        return
    }
    router.push(item.route)
    emit('close-mobile')
}

// Lucide SVG icon paths (inline to avoid external dependency)
const iconMap: Record<string, string> = {
    grid: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
    users: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    building: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M9 3h6v18H9z"/><path d="M16 9h4v12H16z"/><path d="M4 9h4v12H4z"/></svg>',
    book: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>',
    activity: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    chart: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    settings: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    logout: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    chevronLeft: '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>',
}
</script>

<template>
    <!-- Mobile overlay -->
    <div
        v-if="mobileOpen"
        class="fixed inset-0 z-40 bg-black/50 transition-opacity duration-300 lg:hidden"
        @click="emit('close-mobile')"
    />

    <!-- Sidebar -->
    <aside
        class="fixed top-0 left-0 z-50 h-full bg-white border-r border-gray-200 shadow-sm transition-all duration-300 ease-in-out flex flex-col dark:bg-gray-900 dark:border-gray-700"
        :class="[
            collapsed && !mobileOpen ? 'w-16' : 'w-64',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
            'lg:translate-x-0',
        ]"
    >
        <!-- Logo area -->
        <div class="flex items-center h-16 px-4 border-b border-gray-100 dark:border-gray-700 shrink-0" :class="collapsed && !mobileOpen ? 'justify-center' : 'gap-3'">
            <div v-if="!collapsed || mobileOpen" class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 font-bold text-white rounded-lg bg-blue-600 text-sm">CE</div>
                <span class="text-sm font-bold text-gray-800 dark:text-white whitespace-nowrap">ClassEngage</span>
            </div>
            <div v-else class="flex items-center justify-center w-8 h-8 font-bold text-white rounded-lg bg-blue-600 text-sm">CE</div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-thin">
            <!-- Section label -->
            <div v-if="!collapsed || mobileOpen" class="px-3 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase dark:text-gray-500">
                Main Menu
            </div>

            <!-- Main items -->
            <div
                v-for="item in mainMenuItems"
                :key="item.label"
                @click="navigateTo(item)"
                class="flex items-center gap-3 px-3 py-2.5 mb-0.5 rounded-lg cursor-pointer transition-all duration-150 group"
                :class="[
                    isActive(item.route)
                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800',
                    collapsed && !mobileOpen ? 'justify-center' : '',
                ]"
                :title="item.label"
            >
                <span class="shrink-0" :class="isActive(item.route) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" v-html="iconMap[item.icon]" />
                <span v-if="!collapsed || mobileOpen" class="text-sm font-medium truncate">{{ item.label }}</span>
                <span
                    v-if="item.badge && (!collapsed || mobileOpen)"
                    class="ml-auto text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="isActive(item.route)
                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200'
                        : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                >
                    {{ item.badge }}
                </span>
            </div>

            <!-- Divider -->
            <div v-if="!collapsed || mobileOpen" class="my-4 border-t border-gray-100 dark:border-gray-700" />

            <!-- Section label -->
            <div v-if="!collapsed || mobileOpen" class="px-3 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase dark:text-gray-500">
                Other
            </div>

            <!-- Bottom items -->
            <div
                v-for="item in bottomMenuItems"
                :key="item.label"
                @click="navigateTo(item)"
                class="flex items-center gap-3 px-3 py-2.5 mb-0.5 rounded-lg cursor-pointer transition-all duration-150 group"
                :class="[
                    isActive(item.route)
                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800',
                    collapsed && !mobileOpen ? 'justify-center' : '',
                ]"
                :title="item.label"
            >
                <span class="shrink-0" :class="isActive(item.route) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" v-html="iconMap[item.icon]" />
                <span v-if="!collapsed || mobileOpen" class="text-sm font-medium truncate">{{ item.label }}</span>
            </div>
        </nav>

        <!-- Collapse toggle (desktop only) -->
        <div
            class="hidden lg:flex items-center justify-center h-10 border-t border-gray-100 dark:border-gray-700 cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            @click="emit('toggle-collapse')"
            :title="collapsed ? 'Expand' : 'Collapse'"
        >
            <span :class="collapsed ? '' : 'rotate-180'" class="transition-transform duration-300" v-html="iconMap['chevronLeft']" />
        </div>
    </aside>
</template>
