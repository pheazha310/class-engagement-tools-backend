<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { get, put } from '@/services/api'
import Form from '@/pages/admin/roles/Form.vue'

const route = useRoute()
const router = useRouter()

const role = ref<{ id: number; name: string; permissions: string[]; is_protected: boolean } | null>(null)
const permissions = ref<string[]>([])
const loading = ref(true)

onMounted(async () => {
    const [roleRes, permsRes] = await Promise.all([
        get<any>(`/api/admin/roles/${route.params.id}`),
        get<string[]>('/api/admin/roles/permissions/all'),
    ])
    if (roleRes.data) role.value = roleRes.data
    if (permsRes.data) permissions.value = permsRes.data
    loading.value = false
})

async function onSubmit(data: { name: string; permissions: string[] }) {
    const res = await put(`/api/admin/roles/${route.params.id}`, data)
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Role</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" v-if="role">Adjust permissions for {{ role.name }}</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading role data...
        </div>

        <Form
            v-else-if="role"
            :permissions="permissions"
            :role="role"
            submit-label="Save Changes"
            @submit="onSubmit"
            @cancel="router.push('/admin/dashboard/roles')"
        />

        <div v-else class="text-center py-10 text-gray-400">Role not found.</div>
    </div>
</template>
