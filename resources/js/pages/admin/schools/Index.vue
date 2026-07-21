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
const filterCountryId = ref<string>('all')
const filterProvinceId = ref<string>('all')
const showAddDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedSchool = ref<School | null>(null)
const loading = ref(false)
const saving = ref(false)
const page = ref(1)

const form = ref({
    school_name: '',
    country_id: null as number | null,
    province_id: null as number | null,
})

const kpiStats = computed(() => [
    { label: 'Total Schools', value: total.value, color: '--color-primary' },
])

const total = ref(0)

const availableProvinces = computed(() => {
    if (filterCountryId.value === 'all') return provinces.value
    return provinces.value.filter(p => p.country_id === Number(filterCountryId.value))
})

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
    }
    loading.value = false
}

async function fetchLookup() {
    const res = await get<{ countries: Country[]; provinces: Province[] }>('/api/admin/schools/lookup/data')
    if (res.data) {
        countries.value = res.data.countries
        provinces.value = res.data.provinces
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
            toast.error(res.error.message)
        }
    } else {
        const res = await post('/api/admin/schools', form.value)
        if (res.data) {
            toast.success(`School "${form.value.school_name}" has been added`)
            showAddDialog.value = false
            await fetchSchools()
        } else if (res.error) {
            toast.error(res.error.message)
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
        toast.error(res.error.message)
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

onMounted(() => {
    fetchSchools()
    fetchLookup()
})
</script>

<template>
    <div class="page-content anim-fade-in">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-header-title">School Management</h1>
                <p class="page-header-subtitle">Add, edit, and manage educational institutions on the platform</p>
            </div>
            <div class="page-header-actions">
                <button class="btn btn--primary btn--sm" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add School
                </button>
            </div>
        </div>

        <!-- KPI Stats -->
        <div class="kpi-grid">
            <div v-for="stat in kpiStats" :key="stat.label" class="kpi-card">
                <div class="kpi-value" :style="{ color: `var(${stat.color})` }">{{ stat.value }}</div>
                <div class="kpi-label">{{ stat.label }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-wrap" style="max-width: 280px;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input v-model="searchQuery" type="text" class="form-input search-input" placeholder="Search school, province, or country..." @input="onSearchInput" />
            </div>
            <div class="filter-group">
                <select v-model="filterCountryId" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Countries</option>
                    <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model="filterProvinceId" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Provinces</option>
                    <option v-for="p in availableProvinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="card">
            <div class="empty-state">
                <div class="empty-icon">⏳</div>
                <h3 class="empty-title">Loading schools...</h3>
            </div>
        </div>

        <!-- Desktop Table -->
        <div v-else-if="schools.length > 0" class="card school-table-card">
            <div class="table-wrap">
                <table class="table school-table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Province</th>
                            <th>Country</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="school in schools" :key="school.id" class="school-row">
                            <td>
                                <div class="table-user">
                                    <div class="avatar avatar--md school-avatar" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                                        {{ school.school_name.charAt(0) }}
                                    </div>
                                    <div class="table-user-info">
                                        <div class="table-user-name">{{ school.school_name }}</div>
                                        <div class="table-user-email">{{ school.created_at }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="school-province">{{ school.province }}</td>
                            <td>
                                <span class="country-cell">{{ school.country }}</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn--ghost btn--icon btn--sm action-edit" title="Edit" @click="openEdit(school)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn btn--ghost btn--icon btn--sm action-delete" title="Delete" @click="confirmDelete(school)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile School Cards -->
        <div v-if="schools.length > 0" class="school-cards">
            <div v-for="school in schools" :key="school.id" class="school-card">
                <div class="school-card-header">
                    <div class="avatar avatar--md school-avatar" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                        {{ school.school_name.charAt(0) }}
                    </div>
                    <div class="school-card-name-wrap">
                        <div class="school-card-name">{{ school.school_name }}</div>
                        <div class="school-card-since">{{ school.created_at }}</div>
                    </div>
                </div>
                <div class="school-card-body">
                    <div class="school-card-field">
                        <span class="school-card-label">Province</span>
                        <span class="school-card-value">{{ school.province }}</span>
                    </div>
                    <div class="school-card-field">
                        <span class="school-card-label">Country</span>
                        <span class="school-card-value">{{ school.country }}</span>
                    </div>
                </div>
                <div class="school-card-actions">
                    <button class="btn btn--ghost btn--icon btn--sm action-edit" title="Edit" @click="openEdit(school)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn--ghost btn--icon btn--sm action-delete" title="Delete" @click="confirmDelete(school)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && schools.length === 0" class="card">
            <div class="empty-state">
                <div class="empty-icon school-empty-icon">🏫</div>
                <h3 class="empty-title">No schools found</h3>
                <p class="empty-desc">Click "Add School" to create your first school.</p>
                <button class="btn btn--primary" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add School
                </button>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && schools.length > 0" class="pagination">
            <div class="pagination-info">
                Showing <strong>{{ schools.length }}</strong> of <strong>{{ total }}</strong> schools
            </div>
        </div>

        <!-- Add School Dialog -->
        <div v-if="showAddDialog" class="dialog-overlay anim-fade-in" @click.self="showAddDialog = false">
            <div class="dialog">
                <div class="dialog-header">
                    <h3 class="dialog-title">Add New School</h3>
                    <p class="dialog-desc">Enter the details of the new educational institution.</p>
                </div>
                <div class="dialog-body">
                    <div class="form-group">
                        <label class="form-label">School Name</label>
                        <input v-model="form.school_name" type="text" class="form-input" placeholder="e.g. Phnom Penh High School" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select v-model="form.country_id" class="form-input form-select">
                            <option :value="null" disabled>Select country</option>
                            <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <select v-model="form.province_id" class="form-input form-select">
                            <option :value="null" disabled>Select province</option>
                            <option v-for="p in dialogProvinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showAddDialog = false">Cancel</button>
                    <button class="btn btn--primary" :disabled="!form.school_name || !form.country_id || !form.province_id || saving" @click="saveSchool">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ saving ? 'Saving...' : 'Add School' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit School Dialog -->
        <div v-if="showEditDialog && selectedSchool" class="dialog-overlay anim-fade-in" @click.self="showEditDialog = false">
            <div class="dialog">
                <div class="dialog-header">
                    <h3 class="dialog-title">Edit School</h3>
                    <p class="dialog-desc">Update the details of the educational institution.</p>
                </div>
                <div class="dialog-body">
                    <div class="form-group">
                        <label class="form-label">School Name</label>
                        <input v-model="form.school_name" type="text" class="form-input" :placeholder="selectedSchool.school_name" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select v-model="form.country_id" class="form-input form-select">
                            <option :value="null" disabled>Select country</option>
                            <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <select v-model="form.province_id" class="form-input form-select">
                            <option :value="null" disabled>Select province</option>
                            <option v-for="p in dialogProvinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showEditDialog = false">Cancel</button>
                    <button class="btn btn--primary" :disabled="!form.school_name || !form.country_id || !form.province_id || saving" @click="saveSchool">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ saving ? 'Saving...' : 'Update School' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div v-if="showDeleteDialog && selectedSchool" class="dialog-overlay anim-fade-in" @click.self="showDeleteDialog = false">
            <div class="dialog" style="max-width: 400px;">
                <div class="dialog-header">
                    <h3 class="dialog-title">Delete School</h3>
                    <p class="dialog-desc">
                        Are you sure you want to delete <strong>{{ selectedSchool.school_name }}</strong>?
                        This action cannot be undone.
                    </p>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showDeleteDialog = false">Cancel</button>
                    <button class="btn btn--danger" @click="executeDelete">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete School
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
