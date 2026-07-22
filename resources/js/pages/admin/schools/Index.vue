<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import { get, post, put, del } from '@/services/api'

interface Country {
    id: number
    name: string
    code: string
}

interface Province {
    id: number
    name: string
    country_id: number
}

interface School {
    id: number
    school_name: string
    country_id: number
    country: string | null
    province_id: number
    province: string | null
    created_at: string
    updated_at: string
}

interface PaginatedResponse<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
}

const schools = ref<School[]>([])
const countries = ref<Country[]>([])
const provinces = ref<Province[]>([])

const searchQuery = ref('')
const loading = ref(false)
const saving = ref(false)
const page = ref(1)

const showAddDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedSchool = ref<School | null>(null)

const form = ref({
    school_name: '',
    country_id: null as number | null,
    province_id: null as number | null,
})

const total = ref(0)

const dialogProvinces = computed(() => {
    if (!form.value.country_id) return provinces.value
    return provinces.value.filter(p => p.country_id === form.value.country_id)
})

async function fetchSchools() {
    loading.value = true
    const params = new URLSearchParams()
    params.set('page', String(page.value))
    if (searchQuery.value) params.set('search', searchQuery.value)

    const res = await get<PaginatedResponse<School>>(`/api/admin/schools?${params}`)
    if (res.data) {
        schools.value = res.data.data
        total.value = res.data.total
    } else if (res.error) {
        console.error('[Schools] API error:', res.error)
        toast.error(res.error.message || 'Failed to load schools')
    }
    loading.value = false
}

async function fetchLookup() {
    const res = await get<{ countries: Country[]; provinces: Province[] }>('/api/admin/schools/lookup/data')
    if (res.data) {
        countries.value = res.data.countries
        provinces.value = res.data.provinces
    } else if (res.error) {
        console.error('[Schools] Lookup API error:', res.error)
    }
}

function openAdd() {
    form.value = { school_name: '', country_id: null, province_id: null }
    showAddDialog.value = true
}

function openEdit(school: School) {
    form.value = {
        school_name: school.school_name,
        country_id: school.country_id,
        province_id: school.province_id,
    }
    selectedSchool.value = school
    showEditDialog.value = true
}

async function saveSchool() {
    if (!form.value.school_name || !form.value.country_id || !form.value.province_id) return

    saving.value = true

    if (showEditDialog.value && selectedSchool.value) {
        const res = await put(`/api/admin/schools/${selectedSchool.value.id}`, form.value)
        if (res.data) {
            toast.success(`School "${form.value.school_name}" has been updated`)
            showEditDialog.value = false
            selectedSchool.value = null
            await fetchSchools()
        } else if (res.error) {
            toast.error(res.error.message || 'Failed to update school')
        }
    } else {
        const res = await post('/api/admin/schools', form.value)
        if (res.data) {
            toast.success(`School "${form.value.school_name}" has been added`)
            showAddDialog.value = false
            await fetchSchools()
        } else if (res.error) {
            toast.error(res.error.message || 'Failed to add school')
        }
    }

    saving.value = false
}

function confirmDelete(school: School) {
    selectedSchool.value = school
    showDeleteDialog.value = true
}

async function executeDelete() {
    if (!selectedSchool.value) return

    const res = await del(`/api/admin/schools/${selectedSchool.value.id}`)
    if (res.data) {
        toast.success(`School "${selectedSchool.value.school_name}" has been deleted`)
        showDeleteDialog.value = false
        selectedSchool.value = null
        await fetchSchools()
    } else if (res.error) {
        toast.error(res.error.message || 'Failed to delete school')
    }
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null
function onSearchInput() {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        page.value = 1
        fetchSchools()
    }, 300)
}

function goToPage(p: number) {
    page.value = p
    fetchSchools()
}

function getInitials(name: string): string {
    return name.charAt(0).toUpperCase()
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

onMounted(() => {
    fetchSchools()
    fetchLookup()
})
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    School Management
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add, edit, and manage educational institutions on the platform
                </p>
            </div>
            <button
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
                @click="openAdd"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add School
            </button>
        </div>

        <!-- KPI Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ total }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Schools</div>
            </div>
        </div>

        <!-- Search -->
        <div class="relative mb-4 max-w-sm">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
            >
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search school, province, or country..."
                class="w-full h-9 pl-10 pr-4 text-sm bg-white border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all"
                @input="onSearchInput"
            />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="space-y-3">
            <div v-for="i in 4" :key="i" class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse shrink-0" />
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-48" />
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-32" />
                </div>
                <div class="h-8 w-16 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
            </div>
        </div>

        <!-- Schools Table -->
        <div
            v-else-if="schools.length > 0"
            class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80">
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">School</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden sm:table-cell">Province</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden md:table-cell">Country</th>
                            <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="school in schools"
                            :key="school.id"
                            class="group transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/20"
                        >
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        {{ getInitials(school.school_name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[200px]">
                                            {{ school.school_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">
                                            {{ formatDate(school.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ school.province || '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ school.country || '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex justify-end gap-1">
                                    <button
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 rounded-lg hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer"
                                        title="Edit"
                                        @click="openEdit(school)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 rounded-lg hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer"
                                        title="Delete"
                                        @click="confirmDelete(school)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Showing <strong>{{ schools.length }}</strong> of <strong>{{ total }}</strong> schools
                </span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-16 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 mb-4 text-2xl">
                🏫
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No schools found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center max-w-xs">
                {{ searchQuery ? 'No schools match your search criteria.' : 'There are no schools registered yet. Add your first school to get started.' }}
            </p>
            <button
                v-if="!searchQuery"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
                @click="openAdd"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add School
            </button>
        </div>

        <!-- Add School Dialog -->
        <div
            v-if="showAddDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="showAddDialog = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-lg mx-4 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New School</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter the details of the new educational institution.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">School Name</label>
                        <input
                            v-model="form.school_name"
                            type="text"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                            placeholder="e.g. Phnom Penh High School"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                        <select
                            v-model="form.country_id"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                        >
                            <option :value="null" disabled>Select country</option>
                            <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Province</label>
                        <select
                            v-model="form.province_id"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                        >
                            <option :value="null" disabled>Select province</option>
                            <option v-for="p in dialogProvinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-end gap-3">
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                        @click="showAddDialog = false"
                    >
                        Cancel
                    </button>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!form.school_name || !form.country_id || !form.province_id || saving"
                        @click="saveSchool"
                    >
                        <svg v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ saving ? 'Saving...' : 'Add School' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit School Dialog -->
        <div
            v-if="showEditDialog && selectedSchool"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="showEditDialog = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-lg mx-4 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit School</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update the details of the educational institution.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">School Name</label>
                        <input
                            v-model="form.school_name"
                            type="text"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                            placeholder="School name"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                        <select
                            v-model="form.country_id"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                        >
                            <option :value="null" disabled>Select country</option>
                            <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Province</label>
                        <select
                            v-model="form.province_id"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                        >
                            <option :value="null" disabled>Select province</option>
                            <option v-for="p in dialogProvinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-end gap-3">
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                        @click="showEditDialog = false"
                    >
                        Cancel
                    </button>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!form.school_name || !form.country_id || !form.province_id || saving"
                        @click="saveSchool"
                    >
                        <svg v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ saving ? 'Saving...' : 'Update School' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div
            v-if="showDeleteDialog && selectedSchool"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="showDeleteDialog = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-sm mx-4 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">Delete School</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        Are you sure you want to delete <strong class="text-gray-700 dark:text-gray-300">{{ selectedSchool.school_name }}</strong>?
                        This action cannot be undone.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-center gap-3">
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                        @click="showDeleteDialog = false"
                    >
                        Cancel
                    </button>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors cursor-pointer"
                        @click="executeDelete"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete School
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
