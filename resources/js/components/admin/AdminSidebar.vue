<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import {
  LayoutGrid,
  Users,
  ShieldCheck,
  Building2,
  BookOpen,
  BarChart3,
  Activity,
  Settings,
  Lock,
  LogOut,
  ChevronLeft,
} from "@lucide/vue";

interface SidebarItem {
  label: string;
  icon: object;
  route: string;
}

defineProps<{
  collapsed: boolean;
  mobileOpen: boolean;
}>();

const emit = defineEmits<{
  "toggle-collapse": [];
  "close-mobile": [];
}>();

const route = useRoute();
const router = useRouter();

const menuSections: { label: string; items: SidebarItem[] }[] = [
  {
    label: "Dashboard",
    items: [
      { label: "Dashboard", icon: LayoutGrid, route: "/admin/dashboard" },
    ],
  },
  {
    label: "Management",
    items: [
      { label: "Users", icon: Users, route: "/admin/dashboard/users" },
      { label: "Roles", icon: ShieldCheck, route: "/admin/dashboard/roles" },
      { label: "Schools", icon: Building2, route: "/admin/dashboard/schools" },
    ],
  },
  {
    label: "Learning",
    items: [
      { label: "Classes", icon: BookOpen, route: "/admin/dashboard/classes" },
    ],
  },
  {
    label: "Analytics",
    items: [
      { label: "Reports", icon: BarChart3, route: "/admin/dashboard/reports" },
      { label: "Activity Log", icon: Activity, route: "/admin/dashboard/activity" },
    ],
  },
  {
    label: "Settings",
    items: [
      { label: "Admin Settings", icon: Settings, route: "/admin/dashboard/settings" },
      { label: "Security", icon: Lock, route: "/admin/dashboard/security" },
    ],
  },
];

function isActive(itemRoute: string): boolean {
  return route.path === itemRoute || route.path.startsWith(itemRoute + "/");
}

async function handleLogout() {
  try {
    await fetch('/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '' } })
  } catch {
    // fallback to GET if POST fails
  }
  window.location.href = '/login'
}

function navigateTo(item: SidebarItem) {
  router.push(item.route);
  emit("close-mobile");
}
</script>

<template>
  <div
    v-if="mobileOpen"
    class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm transition-opacity lg:hidden"
    @click="emit('close-mobile')"
  />

  <aside
    class="fixed top-0 left-0 z-50 h-full bg-white transition-all duration-300 ease-in-out flex flex-col"
    :class="[
      collapsed && !mobileOpen ? 'w-16' : 'w-[260px]',
      mobileOpen ? 'translate-x-0' : '-translate-x-full',
      'lg:translate-x-0',
    ]"
  >
    <div
      class="flex items-center h-16 px-6 shrink-0"
      :class="collapsed && !mobileOpen ? 'justify-center px-0' : ''"
    >
      <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs">
          CE
        </div>
        <div v-if="!collapsed || mobileOpen" class="flex flex-col">
          <span class="text-sm font-bold text-gray-900 leading-tight">ClassEngage</span>
          <span class="text-[10px] text-gray-400 leading-tight">Admin Portal</span>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-4 py-4 overflow-y-auto scrollbar-thin space-y-8">
      <template v-for="section in menuSections" :key="section.label">
        <div v-if="!collapsed || mobileOpen">
          <div class="px-3 mb-2 text-[11px] font-semibold tracking-widest text-gray-400 uppercase">
            {{ section.label }}
          </div>
        </div>

        <div class="space-y-1">
          <div
            v-for="item in section.items"
            :key="item.label"
            @click="navigateTo(item)"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-150"
            :class="[
              isActive(item.route)
                ? 'bg-blue-50 text-blue-600 font-semibold'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700',
              collapsed && !mobileOpen ? 'justify-center' : '',
            ]"
            :title="item.label"
          >
            <component
              :is="item.icon"
              class="shrink-0"
              :class="[
                isActive(item.route) ? 'text-blue-600' : 'text-gray-400',
                collapsed && !mobileOpen ? 'w-6 h-6' : 'w-5 h-5',
              ]"
            />
            <span v-if="!collapsed || mobileOpen" class="text-sm truncate">{{ item.label }}</span>
          </div>
        </div>
      </template>
    </nav>

    <div class="px-4 py-3">
      <div
        @click="handleLogout"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors text-gray-500 hover:bg-red-50 hover:text-red-600"
        :class="collapsed && !mobileOpen ? 'justify-center' : ''"
        title="Logout"
      >
        <LogOut class="shrink-0 w-5 h-5 text-gray-400" />
        <span v-if="!collapsed || mobileOpen" class="text-sm">Logout</span>
      </div>
    </div>

    <div
      class="hidden lg:flex items-center justify-center h-12 cursor-pointer text-gray-300 hover:text-gray-500 transition-colors"
      @click="emit('toggle-collapse')"
      :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
    >
      <ChevronLeft
        class="w-4 h-4 transition-transform duration-300"
        :class="collapsed ? 'rotate-180' : ''"
      />
    </div>
  </aside>
</template>
