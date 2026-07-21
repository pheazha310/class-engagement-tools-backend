<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { get, post } from '@/services/api'
import Form from '@/pages/admin/roles/Form.vue'

const router = useRouter()
const permissions = ref<string[]>([])
const loading = ref(true)

onMounted(async () => {
    const res = await get<string[]>('/api/admin/roles/permissions/all')
    if (res.data) permissions.value = res.data
    loading.value = false
})

async function onSubmit(data: { name: string; permissions: string[] }) {
    const res = await post('/api/admin/roles', data)
    if (!res.error) {
        router.push('/admin/dashboard/roles')
    } else {
        alert(res.error?.message || 'An error occurred')
    }
}
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Role</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define a new role and its permissions</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading permissions...
        </div>

        <Form
            v-else
            :permissions="permissions"
            submit-label="Create Role"
            @submit="onSubmit"
            @cancel="router.push('/admin/dashboard/roles')"
        />
    </div>
</template>
