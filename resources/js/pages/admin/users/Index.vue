<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { del, get } from '@/services/api'

interface User {
    id: string
    name: string
    email: string
    email_verified_at: string | null
    roles: string[]
    school_name: string
    country_name: string
    province_name: string
    created_at: string
}

interface PaginatedResponse<T> {
    data: T[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

const router = useRouter()
const users = ref<User[]>([])
const loading = ref(true)
const search = ref('')
const pagination = ref<{
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}>({
    current_page: 1,
    last_page: 1,
    from: null,
    to: null,
    total: 0,
    links: [],
})

let searchTimeout: ReturnType<typeof setTimeout> | undefined

watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => fetchUsers(), 300)
})

async function fetchUsers(page = 1) {
    loading.value = true
    const params = new URLSearchParams()
    params.set('page', String(page))
    if (search.value) params.set('search', search.value)

    const response = await get<PaginatedResponse<User>>(`/api/admin/users?${params}`)
    if (response.data) {
        users.value = response.data.data
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            links: response.data.links,
        }
    }
    loading.value = false
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchUsers(page)
    }
}

async function deleteUser(user: User) {
    if (!confirm(`Delete user "${user.name}"? This cannot be undone.`)) return
    const response = await del(`/api/admin/users/${user.id}`)
    if (!response.error) {
        fetchUsers(pagination.value.current_page)
    } else {
        alert(response.error)
    }
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

onMounted(() => fetchUsers())
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage registered users and their roles</p>
            </div>
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                @click="router.push('/admin/dashboard/users/create')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add User
            </button>
        </div>

        <!-- Search -->
        <div class="relative mb-4 max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                v-model="search"
                type="text"
                placeholder="Search by name or email..."
                class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
            />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center gap-2.5 py-4 text-sm text-gray-400 dark:text-gray-500">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin dark:border-gray-700 dark:border-t-blue-400"></div>
            Loading users...
        </div>

        <!-- Users Table -->
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50 dark:bg-gray-800/50 text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Roles</th>
                        <th class="px-4 py-3 font-medium">School</th>
                        <th class="px-4 py-3 font-medium">Country / Province</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role"
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
                                    :class="role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'"
                                >
                                    {{ role }}
                                </span>
                                <span v-if="user.roles.length === 0" class="text-xs text-gray-400">-</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ user.school_name || '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ user.country_name }} / {{ user.province_name }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(user.created_at) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <button
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Edit"
                                    @click="router.push(`/admin/dashboard/users/${user.id}/edit`)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button
                                    class="inline-flex items-center justify-center w-8 h-8 text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete"
                                    @click="deleteUser(user)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                            No users found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1 && !loading" class="flex items-center justify-between mt-4 text-sm text-gray-500 dark:text-gray-400">
            <span>Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }}</span>
            <div class="flex gap-1">
                <button
                    v-for="link in pagination.links"
                    :key="link.label"
                    class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors"
                    :class="link.active
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700'"
                    :disabled="!link.url"
                    @click="link.url && fetchUsers(new URL(link.url).searchParams.get('page') ? Number(new URL(link.url).searchParams.get('page')) : 1)"
                >
                    {{ link.label.replace('&laquo;', '‹').replace('&raquo;', '›').replace(/&[a-z]+;/g, '').trim() }}
                </button>
            </div>
        </div>
    </div>
</template>
