<script setup lang="ts">
import { computed } from 'vue'
import { UserPlus, Building2, BookOpen, BarChart3, Plus } from '@lucide/vue'

const props = defineProps<{
  label: string
  icon: string
  variant: 'primary' | 'success' | 'warning' | 'info'
  route: string
}>()

const emit = defineEmits<{
  click: [route: string]
}>()

const iconMap: Record<string, object> = {
  'user-plus': UserPlus,
  building: Building2,
  'book-open': BookOpen,
  'bar-chart': BarChart3,
}

const variantConfig = computed(() => {
  const map: Record<string, { hover: string, iconColor: string }> = {
    primary: { hover: 'hover:bg-blue-50', iconColor: 'text-blue-600' },
    success: { hover: 'hover:bg-emerald-50', iconColor: 'text-emerald-600' },
    warning: { hover: 'hover:bg-amber-50', iconColor: 'text-amber-600' },
    info: { hover: 'hover:bg-sky-50', iconColor: 'text-sky-600' },
  }
  return map[props.variant] || map.primary
})
</script>

<template>
  <button
    class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
    :class="variantConfig.hover"
    @click="emit('click', route)"
  >
    <component :is="iconMap[icon] || Plus" class="w-4 h-4" :class="variantConfig.iconColor" />
    <span>{{ label }}</span>
  </button>
</template>
