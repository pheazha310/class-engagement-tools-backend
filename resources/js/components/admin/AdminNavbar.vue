<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAdminDashboardStore } from '@/stores/useAdminDashboardStore'
import { useAppearance } from '@/composables/useAppearance'

const props = defineProps<{
    collapsed?: boolean
}>()

const emit = defineEmits<{
    'toggle-mobile': []
}>()

const store = useAdminDashboardStore()
const { resolvedAppearance, updateAppearance } = useAppearance()

const isDark = computed(() => resolvedAppearance.value === 'dark')
const showNotifications = ref(false)
const showProfileMenu = ref(false)

function toggleTheme() {
    updateAppearance(isDark.value ? 'light' : 'dark')
}

function handleLogout() {
    window.location.href = '/logout'
}
</script>

<template>
    <header class="fixed top-0 left-0 right-0 z-30 h-16 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-700 transition-all duration-300"
        :class="props.collapsed ? 'lg:left-16' : 'lg:left-64'"
    >
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
            <!-- Left: Mobile toggle + Title -->
            <div class="flex items-center gap-3">
                <button
                    class="flex items-center justify-center w-9 h-9 text-gray-500 rounded-lg hover:bg-gray-100 lg:hidden dark:hover:bg-gray-800 transition-colors"
                    @click="emit('toggle-mobile')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                </button>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Admin Dashboard</h2>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <!-- Search bar (desktop) -->
                <div class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 w-64 dark:bg-gray-800 dark:border-gray-600 transition-all focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 dark:focus-within:ring-blue-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" placeholder="Search anything..." class="w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none dark:text-gray-200 dark:placeholder-gray-500" />
                </div>

                <!-- Theme toggle -->
                <button
                    class="flex items-center justify-center w-9 h-9 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    @click="toggleTheme"
                    :title="isDark ? 'Light mode' : 'Dark mode'"
                >
                    <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <!-- Notifications -->
                <div class="relative">
                    <button
                        class="relative flex items-center justify-center w-9 h-9 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        @click="showNotifications = !showNotifications"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 border-2 border-white rounded-full dark:border-gray-900" />
                    </button>

                    <!-- Notifications dropdown -->
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-lg z-50 dark:bg-gray-800 dark:border-gray-700 overflow-hidden"
                        @mouseleave="showNotifications = false"
                    >
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white">Notifications</h4>
                            <button class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400" @click="showNotifications = false">Mark all read</button>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <div
                                v-for="notif in store.notifications"
                                :key="notif.id"
                                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                            >
                                <div class="flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 shrink-0 text-base">
                                    {{ notif.icon }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-snug" v-html="notif.text" />
                                    <p class="mt-0.5 text-xs text-gray-400">{{ notif.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="relative">
                    <button
                        class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        @click="showProfileMenu = !showProfileMenu"
                    >
                        <div class="flex items-center justify-center w-8 h-8 text-sm font-semibold text-white rounded-full bg-blue-600 shrink-0">
                            {{ store.currentUser.initials }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-gray-800 dark:text-white leading-tight">{{ store.currentUser.name }}</p>
                            <p class="text-xs text-gray-400 leading-tight">{{ store.currentUser.role }}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden sm:block w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <!-- Profile dropdown -->
                    <div
                        v-if="showProfileMenu"
                        class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-50 dark:bg-gray-800 dark:border-gray-700 overflow-hidden"
                        @mouseleave="showProfileMenu = false"
                    >
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ store.currentUser.name }}</p>
                            <p class="text-xs text-gray-400">{{ store.currentUser.email }}</p>
                        </div>
                        <div class="p-1">
                            <button class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                My Profile
                            </button>
                            <button class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Settings
                            </button>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700 p-1">
                            <button
                                @click="handleLogout"
                                class="flex items-center gap-3 w-full px-3 py-2 text-sm text-red-600 rounded-lg hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
