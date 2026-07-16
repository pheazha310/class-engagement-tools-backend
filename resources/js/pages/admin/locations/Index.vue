<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { del, get } from '@/services/api'

interface SchoolName {
    id: number
    school_name: string
    country: string
    province: string
    created_at: string | null
    updated_at: string | null
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
const items = ref<SchoolName[]>([])
const loading = ref(true)
const search = ref('')
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: null as number | null,
    to: null as number | null,
    total: 0,
    links: [] as { url: string | null; label: string; active: boolean }[],
})

let searchTimeout: ReturnType<typeof setTimeout> | undefined

watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => fetchData(), 300)
})

async function fetchData(page = 1) {
    loading.value = true
    const params = new URLSearchParams()
    params.set('page', String(page))
    if (search.value) params.set('search', search.value)

    const res = await get<PaginatedResponse<SchoolName>>(`/api/admin/locations?${params}`)
    if (res.data) {
        items.value = res.data.data
        pagination.value = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total,
            links: res.data.links,
        }
    }
    loading.value = false
}

async function deleteItem(item: SchoolName) {
    if (!confirm(`Delete "${item.school_name}"? This cannot be undone.`)) return
    const res = await del(`/api/admin/locations/${item.id}`)
    if (!res.error) {
        fetchData(pagination.value.current_page)
    } else {
        alert(res.error)
    }
}

onMounted(() => fetchData())
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">School Names</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage registered school names</p>
            </div>
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                @click="router.push('/admin/dashboard/school-names/create')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add School Name
            </button>
        </div>

        <!-- Search -->
        <div class="relative mb-4 max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                v-model="search"
                type="text"
                placeholder="Search by school name, country, or province..."
                class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
            />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading school names...
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50 dark:bg-gray-800/50 text-left text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">School Name</th>
                        <th class="px-4 py-3 font-medium">Country</th>
                        <th class="px-4 py-3 font-medium">Province</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.school_name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ item.country }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ item.province }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <button
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Edit"
                                    @click="router.push(`/admin/dashboard/school-names/${item.id}/edit`)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button
                                    class="inline-flex items-center justify-center w-8 h-8 text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete"
                                    @click="deleteItem(item)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">No school names found.</td>
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
                    @click="link.url && fetchData(new URL(link.url).searchParams.get('page') ? Number(new URL(link.url).searchParams.get('page')) : 1)"
                >
                    {{ link.label.replace('&laquo;', '‹').replace('&raquo;', '›').replace(/&[a-z]+;/g, '').trim() }}
                </button>
            </div>
        </div>
    </div>
</template>
