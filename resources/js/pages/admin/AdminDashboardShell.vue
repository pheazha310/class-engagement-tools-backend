<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Toaster } from 'vue-sonner'
import { ChevronRight, Home } from '@lucide/vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import AdminNavbar from '@/components/admin/AdminNavbar.vue'

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)

const route = useRoute()

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

function toggleMobile() {
  mobileOpen.value = !mobileOpen.value
}

function closeMobile() {
  mobileOpen.value = false
}

interface BreadcrumbItem {
  label: string
  path: string
}

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
  const path = route.path
  const segments = path.split('/').filter(Boolean)

  const labelMap: Record<string, string> = {
    'admin': 'Admin',
    'dashboard': 'Dashboard',
    'users': 'Users',
    'create': 'Create',
    'edit': 'Edit',
    'roles': 'Roles',
    'school-names': 'School Names',
    'schools': 'Schools',
    'classes': 'Classes',
    'activity': 'Activity Log',
    'reports': 'Reports',
    'settings': 'Settings',
    'security': 'Security',
  }

  const items: BreadcrumbItem[] = []

  let currentPath = ''
  for (let i = 0; i < segments.length; i++) {
    const seg = segments[i]
    const idMatch = seg.match(/^\d+$/)
    currentPath += '/' + seg

    if (idMatch) {
      items.push({ label: `#${seg}`, path: currentPath })
    } else {
      items.push({ label: labelMap[seg] || seg.charAt(0).toUpperCase() + seg.slice(1), path: currentPath })
    }
  }

  return items
})
</script>

<template>
  <div class="min-h-screen bg-[#F8FAFC]">
    <Toaster
      position="top-right"
      :duration="4000"
      :close-button="true"
      :rich-colors="true"
      class="z-[100]"
    />

    <AdminSidebar
      :collapsed="sidebarCollapsed"
      :mobile-open="mobileOpen"
      @toggle-collapse="toggleCollapse"
      @close-mobile="closeMobile"
    />

    <AdminNavbar
      :collapsed="sidebarCollapsed"
      @toggle-mobile="toggleMobile"
    />

    <main
      class="pt-16 min-h-screen transition-all duration-300"
      :class="sidebarCollapsed ? 'lg:ml-[68px]' : 'lg:ml-[260px]'"
    >
      <div class="px-4 md:px-6 lg:px-8 pt-4 pb-0">
        <nav v-if="breadcrumbs.length > 1" class="flex items-center gap-1.5 text-sm text-gray-400 mb-4">
          <router-link
            to="/admin/dashboard"
            class="flex items-center gap-1 hover:text-blue-600 transition-colors"
          >
            <Home class="w-3.5 h-3.5" />
          </router-link>
          <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
            <ChevronRight class="w-3.5 h-3.5 text-gray-300" />
            <router-link
              v-if="index < breadcrumbs.length - 1"
              :to="crumb.path"
              class="hover:text-blue-600 transition-colors truncate max-w-[120px]"
            >
              {{ crumb.label }}
            </router-link>
            <span
              v-else
              class="text-gray-700 font-medium truncate max-w-[160px]"
            >
              {{ crumb.label }}
            </span>
          </template>
        </nav>
      </div>

      <div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto">
        <router-view />
      </div>
    </main>
  </div>
</template>
