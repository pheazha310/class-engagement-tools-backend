<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { del, get } from '@/services/api'

interface Role {
    id: number
    name: string
    users_count: number
    permissions: string[]
    is_protected: boolean
}

const router = useRouter()
const roles = ref<Role[]>([])
const loading = ref(true)

onMounted(async () => {
    const res = await get<Role[]>('/api/admin/roles')
    if (res.data) roles.value = res.data
    loading.value = false
})

async function deleteRole(role: Role) {
    if (!confirm(`Delete the "${role.name}" role?`)) return
    const res = await del(`/api/admin/roles/${role.id}`)
    if (!res.error) {
        roles.value = roles.value.filter((r) => r.id !== role.id)
    } else {
        alert(res.error?.message || 'An error occurred')
    }
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Role Management</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define roles and their permissions</p>
            </div>
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                @click="router.push('/admin/dashboard/roles/create')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Role
            </button>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading roles...
        </div>

        <div v-else-if="roles.length === 0" class="text-center py-10 text-gray-400">No roles found.</div>

        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="role in roles"
                :key="role.id"
                class="flex flex-col gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white capitalize">{{ role.name }}</h3>
                        <svg v-if="role.is_protected" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="flex gap-1">
                        <button
                            v-if="!role.is_protected"
                            class="inline-flex items-center justify-center w-8 h-8 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            title="Edit"
                            @click="router.push(`/admin/dashboard/roles/${role.id}/edit`)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button
                            v-if="!role.is_protected"
                            class="inline-flex items-center justify-center w-8 h-8 text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Delete"
                            @click="deleteRole(role)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ role.users_count }} user{{ role.users_count === 1 ? '' : 's' }}
                </p>

                <div class="flex flex-wrap gap-1">
                    <span
                        v-for="permission in role.permissions"
                        :key="permission"
                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        {{ permission }}
                    </span>
                    <span v-if="role.permissions.length === 0" class="text-sm text-gray-400">
                        No permissions
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
