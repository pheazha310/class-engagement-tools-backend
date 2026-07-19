<script setup lang="ts">
import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'

// ─── Types ────────────────────────────────────────────────
interface School {
    id: number
    name: string
    code: string
    address: string
    province: string
    country: string
    type: 'public' | 'private' | 'international'
    status: 'active' | 'pending' | 'inactive'
    teachers: number
    students: number
    created_at: string
}

// ─── Mock Data ────────────────────────────────────────────
const schools = ref<School[]>([
    { id: 1, name: 'Phnom Penh High School', code: 'PPHS', address: '123 Monivong Blvd', province: 'Phnom Penh', country: 'Cambodia', type: 'public', status: 'active', teachers: 45, students: 1200, created_at: 'Jan 15, 2024' },
    { id: 2, name: 'Sisowath High School', code: 'SHS', address: '456 Norodom Blvd', province: 'Phnom Penh', country: 'Cambodia', type: 'public', status: 'active', teachers: 38, students: 980, created_at: 'Feb 3, 2024' },
    { id: 3, name: 'Bak Touk High School', code: 'BTHS', address: '789 Street 271', province: 'Phnom Penh', country: 'Cambodia', type: 'private', status: 'active', teachers: 28, students: 650, created_at: 'Mar 12, 2024' },
    { id: 4, name: 'Siem Reap International School', code: 'SRIS', address: '12 Achar Mean St', province: 'Siem Reap', country: 'Cambodia', type: 'international', status: 'pending', teachers: 22, students: 480, created_at: 'Apr 8, 2024' },
    { id: 5, name: 'Battambang Regional College', code: 'BRC', address: '89 Street 1.5', province: 'Battambang', country: 'Cambodia', type: 'public', status: 'active', teachers: 32, students: 780, created_at: 'May 20, 2024' },
    { id: 6, name: 'Kampong Cham Academy', code: 'KCA', address: '56 Riverside Road', province: 'Kampong Cham', country: 'Cambodia', type: 'private', status: 'inactive', teachers: 18, students: 340, created_at: 'Jun 5, 2024' },
    { id: 7, name: 'Sunrise International School', code: 'SIS', address: '234 Sunrise Ave', province: 'Phnom Penh', country: 'Cambodia', type: 'international', status: 'active', teachers: 35, students: 890, created_at: 'Jul 14, 2024' },
    { id: 8, name: 'Preah Sihanouk High School', code: 'PSHS', address: '78 Beach Road', province: 'Preah Sihanouk', country: 'Cambodia', type: 'public', status: 'pending', teachers: 15, students: 310, created_at: 'Aug 1, 2024' },
])

const searchQuery = ref('')
const filterType = ref<string>('all')
const filterStatus = ref<string>('all')
const showAddDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedSchool = ref<School | null>(null)

const newSchool = ref({
    name: '',
    code: '',
    address: '',
    province: '',
    country: 'Cambodia',
    type: 'public' as 'public' | 'private' | 'international',
})

const filteredSchools = computed(() => {
    return schools.value.filter(s => {
        const matchSearch = !searchQuery.value
            || s.name.toLowerCase().includes(searchQuery.value.toLowerCase())
            || s.code.toLowerCase().includes(searchQuery.value.toLowerCase())
            || s.province.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchType = filterType.value === 'all' || s.type === filterType.value
        const matchStatus = filterStatus.value === 'all' || s.status === filterStatus.value

        return matchSearch && matchType && matchStatus
    })
})

const kpiStats = computed(() => [
    { label: 'Total Schools', value: schools.value.length, color: '--color-primary' },
    { label: 'Active', value: schools.value.filter(s => s.status === 'active').length, color: '--color-success' },
    { label: 'Pending', value: schools.value.filter(s => s.status === 'pending').length, color: '--color-warning' },
    { label: 'Inactive', value: schools.value.filter(s => s.status === 'inactive').length, color: '--color-muted' },
])

const typeColors: Record<string, string> = {
    public: 'badge--info',
    private: 'badge--warning',
    international: 'badge--primary',
}

const statusColors: Record<string, string> = {
    active: 'badge--success',
    pending: 'badge--warning',
    inactive: 'badge--muted',
}

function openAdd() {
    newSchool.value = { name: '', code: '', address: '', province: '', country: 'Cambodia', type: 'public' }
    showAddDialog.value = true
}

function addSchool() {
    if (!newSchool.value.name || !newSchool.value.code) return

    schools.value.unshift({
        id: Math.max(...schools.value.map(s => s.id)) + 1,
        ...newSchool.value,
        status: 'pending',
        teachers: 0,
        students: 0,
        created_at: 'Just now',
    })

    showAddDialog.value = false
    toast.success(`School "${newSchool.value.name}" has been added`)
}

function confirmDelete(school: School) {
    selectedSchool.value = school
    showDeleteDialog.value = true
}

function executeDelete() {
    if (!selectedSchool.value) return
    schools.value = schools.value.filter(s => s.id !== selectedSchool.value!.id)
    toast.success(`School "${selectedSchool.value.name}" has been deleted`)
    showDeleteDialog.value = false
    selectedSchool.value = null
}

const getTypeText: Record<string, string> = {
    public: 'Public',
    private: 'Private',
    international: 'International',
}
</script>

<template>
    <div class="page-content anim-fade-in">
        <!-- Alert banner -->
        <div class="alert-banner alert-banner--warning">
            <svg class="alert-banner-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="alert-badge">DEMO</span>
            <span>Showing mock data — connect your API to manage real schools.</span>
        </div>

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-header-title">School Management</h1>
                <p class="page-header-subtitle">Add, edit, and manage educational institutions on the platform</p>
            </div>
            <div class="page-header-actions">
                <button class="btn btn--outline btn--sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 15 21 21 15 21"/><polyline points="3 9 3 3 9 3"/><rect x="5" y="5" width="14" height="14"/></svg>
                    Bulk Import
                </button>
                <button class="btn btn--primary btn--sm" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add School
                </button>
            </div>
        </div>

        <!-- KPI Stats -->
        <div class="mini-stat-grid" style="margin-bottom: 20px;">
            <div v-for="stat in kpiStats" :key="stat.label" class="mini-stat">
                <div class="mini-stat-value" :style="{ color: `var(${stat.color})` }">{{ stat.value }}</div>
                <div class="mini-stat-label">{{ stat.label }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-wrap" style="max-width: 300px;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input v-model="searchQuery" type="text" class="form-input search-input" placeholder="Search schools..." />
            </div>
            <div class="filter-group">
                <select v-model="filterType" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Types</option>
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                    <option value="international">International</option>
                </select>
                <select v-model="filterStatus" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Schools Table -->
        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="school in filteredSchools" :key="school.id">
                            <td>
                                <div class="table-user">
                                    <div class="avatar avatar--md" style="background: linear-gradient(135deg, #4f46e5, #6366f1); border-radius: 10px;">
                                        {{ school.name.charAt(0) }}
                                    </div>
                                    <div class="table-user-info">
                                        <div class="table-user-name">{{ school.name }}</div>
                                        <div class="table-user-email">Since {{ school.created_at }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span style="font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ school.code }}</span></td>
                            <td><span class="badge" :class="typeColors[school.type]">{{ getTypeText[school.type] }}</span></td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-secondary);">{{ school.province }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ school.country }}</div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 12px; font-size: 12px;">
                                    <span style="color: var(--text-muted);">👨‍🏫 {{ school.teachers }}</span>
                                    <span style="color: var(--text-muted);">🎓 {{ school.students }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" :class="statusColors[school.status]">
                                    <span class="badge-dot" :class="'badge-dot--' + school.status" />
                                    {{ school.status }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn--ghost btn--icon btn--sm" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn btn--ghost btn--icon btn--sm btn-danger-ghost" title="Delete" @click="confirmDelete(school)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredSchools.length === 0" class="empty-state">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <h3 class="empty-title">No schools found</h3>
                <p class="empty-desc">{{ searchQuery ? 'No schools match your search. Try a different term.' : 'No schools registered yet. Add your first school to get started.' }}</p>
                <button v-if="!searchQuery" class="btn btn--primary" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add School
                </button>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Showing <strong>{{ filteredSchools.length }}</strong> of <strong>{{ schools.length }}</strong> schools
                </div>
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
                        <input v-model="newSchool.name" type="text" class="form-input" placeholder="e.g. Phnom Penh High School" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">School Code</label>
                            <input v-model="newSchool.code" type="text" class="form-input" placeholder="e.g. PPHS" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select v-model="newSchool.type" class="form-input form-select">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                                <option value="international">International</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input v-model="newSchool.address" type="text" class="form-input" placeholder="e.g. 123 Monivong Blvd" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">Province</label>
                            <input v-model="newSchool.province" type="text" class="form-input" placeholder="e.g. Phnom Penh" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input v-model="newSchool.country" type="text" class="form-input" placeholder="e.g. Cambodia" />
                        </div>
                    </div>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showAddDialog = false">Cancel</button>
                    <button class="btn btn--primary" :disabled="!newSchool.name || !newSchool.code" @click="addSchool">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Add School
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
                        Are you sure you want to delete <strong>{{ selectedSchool.name }}</strong>?
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
