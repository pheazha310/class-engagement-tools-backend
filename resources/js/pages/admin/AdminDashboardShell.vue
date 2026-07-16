<script setup lang="ts">
import { ref } from 'vue'
import { Toaster } from 'vue-sonner'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import AdminNavbar from '@/components/admin/AdminNavbar.vue'

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)

function toggleCollapse() {
    sidebarCollapsed.value = !sidebarCollapsed.value
}

function toggleMobile() {
    mobileOpen.value = !mobileOpen.value
}

function closeMobile() {
    mobileOpen.value = false
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Toast notifications -->
        <Toaster
            position="top-right"
            :duration="4000"
            :close-button="true"
            :rich-colors="true"
            class="z-[100]"
        />

        <!-- Sidebar -->
        <AdminSidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileOpen"
            @toggle-collapse="toggleCollapse"
            @close-mobile="closeMobile"
        />

        <!-- Navbar -->
        <AdminNavbar
            :collapsed="sidebarCollapsed"
            @toggle-mobile="toggleMobile"
        />

        <!-- Main content -->
        <main
            class="pt-16 min-h-screen transition-all duration-300"
            :class="sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'"
        >
            <div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto">
                <router-view />
            </div>
        </main>
    </div>
</template>
