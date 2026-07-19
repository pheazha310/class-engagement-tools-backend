<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAdminDashboardStore } from '@/stores/useAdminDashboardStore'
import { useAppearance } from '@/composables/useAppearance'
import { Search, Bell, Sun, Moon, LogOut, ChevronDown } from '@lucide/vue'

defineProps<{
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
  <header
    class="fixed top-0 left-0 right-0 z-30 h-16 bg-white border-b border-gray-200 transition-all duration-300"
    :class="collapsed ? 'lg:left-[68px]' : 'lg:left-[260px]'"
  >
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
      <div class="flex items-center gap-3">
        <button
          class="flex items-center justify-center w-9 h-9 text-gray-400 rounded-lg hover:bg-gray-100 lg:hidden transition-colors"
          @click="emit('toggle-mobile')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
        </button>
        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
      </div>

      <div class="flex items-center gap-2">
        <div class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 w-64 transition-all focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100">
          <Search class="w-4 h-4 text-gray-400 shrink-0" />
          <input
            type="text"
            placeholder="Search anything..."
            class="w-full bg-transparent text-sm text-gray-600 placeholder-gray-400 outline-none"
          />
          <kbd class="hidden lg:inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium text-gray-400 bg-gray-100 rounded">
            ⌘K
          </kbd>
        </div>

        <button
          class="flex items-center justify-center w-9 h-9 text-gray-400 rounded-xl hover:bg-gray-100 transition-colors"
          @click="toggleTheme"
          :title="isDark ? 'Light mode' : 'Dark mode'"
        >
          <Sun v-if="isDark" class="w-4 h-4" />
          <Moon v-else class="w-4 h-4" />
        </button>

        <div class="relative">
          <button
            class="relative flex items-center justify-center w-9 h-9 text-gray-400 rounded-xl hover:bg-gray-100 transition-colors"
            @click="showNotifications = !showNotifications"
          >
            <Bell class="w-4 h-4" />
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 border-2 border-white rounded-full" />
          </button>

          <div
            v-if="showNotifications"
            class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-2xl shadow-lg z-50 overflow-hidden"
            @mouseleave="showNotifications = false"
          >
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
              <h4 class="text-sm font-semibold text-gray-900">Notifications</h4>
              <button class="text-xs font-medium text-blue-600 hover:text-blue-700" @click="showNotifications = false">Mark all read</button>
            </div>
            <div class="max-h-72 overflow-y-auto">
              <div
                v-for="notif in store.notifications"
                :key="notif.id"
                class="flex items-start gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors"
              >
                <div class="flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 shrink-0 text-base">
                  {{ notif.icon }}
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm text-gray-600 leading-snug" v-html="notif.text" />
                  <p class="mt-0.5 text-xs text-gray-400">{{ notif.time }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="relative">
          <button
            class="flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-xl hover:bg-gray-100 transition-colors"
            @click="showProfileMenu = !showProfileMenu"
          >
            <div class="flex items-center justify-center w-8 h-8 text-sm font-semibold text-white rounded-full bg-blue-600 shrink-0">
              {{ store.currentUser.initials }}
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-medium text-gray-900 leading-tight">{{ store.currentUser.name }}</p>
              <p class="text-xs text-gray-400 leading-tight">{{ store.currentUser.role }}</p>
            </div>
            <ChevronDown class="hidden sm:block w-3.5 h-3.5 text-gray-400" />
          </button>

          <div
            v-if="showProfileMenu"
            class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-2xl shadow-lg z-50 overflow-hidden"
            @mouseleave="showProfileMenu = false"
          >
            <div class="px-5 py-3.5 border-b border-gray-100">
              <p class="text-sm font-medium text-gray-900">{{ store.currentUser.name }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ store.currentUser.email }}</p>
            </div>
            <div class="p-1.5">
              <button class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                My Profile
              </button>
              <button class="flex items-center gap-3 w-full px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                Settings
              </button>
            </div>
            <div class="border-t border-gray-100 p-1.5">
              <button
                @click="handleLogout"
                class="flex items-center gap-3 w-full px-3 py-2 text-sm text-red-600 rounded-lg hover:bg-red-50 transition-colors"
              >
                <LogOut class="w-4 h-4" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
