<script setup lang="ts">
import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'

// ─── Types ────────────────────────────────────────────────
interface ClassItem {
    id: number
    name: string
    section: string
    grade: string
    teacher: string
    school: string
    students: number
    status: 'active' | 'pending' | 'completed'
    schedule: string
    progress: number
    created_at: string
}

// ─── Mock Data ────────────────────────────────────────────
const classes = ref<ClassItem[]>([
    { id: 1, name: 'Mathematics 101', section: 'A', grade: 'Grade 10', teacher: 'Chea Vannak', school: 'Phnom Penh High School', students: 32, status: 'active', schedule: 'Mon, Wed, Fri 8:00 AM', progress: 65, created_at: 'Sep 1, 2024' },
    { id: 2, name: 'English Literature', section: 'B', grade: 'Grade 11', teacher: 'Srey Mom', school: 'Sisowath High School', students: 28, status: 'active', schedule: 'Tue, Thu 10:00 AM', progress: 72, created_at: 'Sep 1, 2024' },
    { id: 3, name: 'Physics', section: 'A', grade: 'Grade 12', teacher: 'Ratanak Vireak', school: 'Bak Touk High School', students: 24, status: 'active', schedule: 'Mon, Wed 1:00 PM', progress: 48, created_at: 'Sep 2, 2024' },
    { id: 4, name: 'Khmer Literature', section: 'C', grade: 'Grade 10', teacher: 'Kosal Bunnarong', school: 'Phnom Penh High School', students: 35, status: 'pending', schedule: 'Tue, Thu, Fri 9:00 AM', progress: 15, created_at: 'Oct 5, 2024' },
    { id: 5, name: 'Chemistry', section: 'B', grade: 'Grade 11', teacher: 'Meas Bora', school: 'Siem Reap International School', students: 20, status: 'active', schedule: 'Wed, Fri 2:00 PM', progress: 55, created_at: 'Sep 15, 2024' },
    { id: 6, name: 'History', section: 'A', grade: 'Grade 9', teacher: 'Dara Pheaktra', school: 'Battambang Regional College', students: 30, status: 'completed', schedule: 'Mon, Thu 11:00 AM', progress: 100, created_at: 'Aug 20, 2024' },
    { id: 7, name: 'Biology', section: 'A', grade: 'Grade 11', teacher: 'Sokha Makara', school: 'Sunrise International School', students: 22, status: 'active', schedule: 'Tue, Fri 8:30 AM', progress: 80, created_at: 'Sep 10, 2024' },
    { id: 8, name: 'Earth Sciences', section: 'B', grade: 'Grade 10', teacher: 'Hun Kim', school: 'Preah Sihanouk High School', students: 18, status: 'pending', schedule: 'Mon, Wed 3:00 PM', progress: 10, created_at: 'Nov 1, 2024' },
])

const searchQuery = ref('')
const filterStatus = ref<string>('all')
const filterGrade = ref<string>('all')
const showAddDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedClass = ref<ClassItem | null>(null)

const newClass = ref({
    name: '',
    section: 'A',
    grade: 'Grade 10',
    teacher: '',
    school: '',
    schedule: '',
})

const filteredClasses = computed(() => {
    return classes.value.filter(c => {
        const matchSearch = !searchQuery.value
            || c.name.toLowerCase().includes(searchQuery.value.toLowerCase())
            || c.teacher.toLowerCase().includes(searchQuery.value.toLowerCase())
            || c.school.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchStatus = filterStatus.value === 'all' || c.status === filterStatus.value
        const matchGrade = filterGrade.value === 'all' || c.grade === filterGrade.value

        return matchSearch && matchStatus && matchGrade
    })
})

const summaryStats = computed(() => [
    { label: 'Total Classes', value: classes.value.length, color: '--color-primary' },
    { label: 'Active', value: classes.value.filter(c => c.status === 'active').length, color: '--color-success' },
    { label: 'Total Students', value: classes.value.reduce((sum, c) => sum + c.students, 0), color: '--color-info' },
    { label: 'Completed', value: classes.value.filter(c => c.status === 'completed').length, color: '--color-muted' },
])

const statusColors: Record<string, string> = {
    active: 'badge--success',
    pending: 'badge--warning',
    completed: 'badge--info',
}

const grades = ['Grade 9', 'Grade 10', 'Grade 11', 'Grade 12']

function openAdd() {
    newClass.value = { name: '', section: 'A', grade: 'Grade 10', teacher: '', school: '', schedule: '' }
    showAddDialog.value = true
}

function addClass() {
    if (!newClass.value.name || !newClass.value.teacher) return

    classes.value.unshift({
        id: Math.max(...classes.value.map(c => c.id)) + 1,
        ...newClass.value,
        students: 0,
        status: 'pending',
        progress: 0,
        created_at: 'Just now',
    })

    showAddDialog.value = false
    toast.success(`Class "${newClass.value.name}" has been created`)
}

function confirmDelete(item: ClassItem) {
    selectedClass.value = item
    showDeleteDialog.value = true
}

function executeDelete() {
    if (!selectedClass.value) return
    classes.value = classes.value.filter(c => c.id !== selectedClass.value!.id)
    toast.success(`Class "${selectedClass.value.name}" has been deleted`)
    showDeleteDialog.value = false
    selectedClass.value = null
}

function getProgressColor(progress: number): string {
    if (progress >= 80) return 'var(--color-success)'
    if (progress >= 40) return 'var(--color-primary)'
    return 'var(--color-warning)'
}
</script>

<template>
    <div class="page-content anim-fade-in">
        <!-- Info banner -->
        <div class="alert-banner alert-banner--info">
            <svg class="alert-banner-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Showing demo data. <strong>Classes</strong> are created by teachers and managed through the platform.</span>
        </div>

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-header-title">Class Management</h1>
                <p class="page-header-subtitle">Create and manage classes, assign teachers, and track progress</p>
            </div>
            <div class="page-header-actions">
                <button class="btn btn--primary btn--sm" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Class
                </button>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="mini-stat-grid" style="margin-bottom: 20px;">
            <div v-for="stat in summaryStats" :key="stat.label" class="mini-stat">
                <div class="mini-stat-value" :style="{ color: `var(${stat.color})` }">{{ stat.value }}</div>
                <div class="mini-stat-label">{{ stat.label }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-wrap" style="max-width: 300px;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input v-model="searchQuery" type="text" class="form-input search-input" placeholder="Search classes..." />
            </div>
            <div class="filter-group">
                <select v-model="filterStatus" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
                <select v-model="filterGrade" class="form-input form-select" style="width: auto; padding-right: 32px;">
                    <option value="all">All Grades</option>
                    <option v-for="g in grades" :key="g" :value="g">{{ g }}</option>
                </select>
            </div>
        </div>

        <!-- Classes Table -->
        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>School</th>
                            <th>Schedule</th>
                            <th>Students</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredClasses" :key="item.id">
                            <td>
                                <div class="table-user">
                                    <div class="avatar avatar--md" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 10px; font-size: 14px;">
                                        {{ item.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() }}
                                    </div>
                                    <div class="table-user-info">
                                        <div class="table-user-name">{{ item.name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            {{ item.grade }} · Section {{ item.section }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="table-user" style="gap: 8px;">
                                    <div class="avatar avatar--sm" style="background: var(--color-success);">
                                        {{ item.teacher.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() }}
                                    </div>
                                    <span style="font-size: 13px; color: var(--text-secondary);">{{ item.teacher }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: var(--text-secondary);">{{ item.school }}</span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ item.schedule }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ item.students }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 100px;">
                                    <div style="flex: 1; height: 6px; background: var(--border-color); border-radius: 3px; overflow: hidden;">
                                        <div
                                            :style="{
                                                width: item.progress + '%',
                                                height: '100%',
                                                background: getProgressColor(item.progress),
                                                borderRadius: '3px',
                                                transition: 'width 0.3s ease',
                                            }"
                                        />
                                    </div>
                                    <span style="font-size: 11px; font-weight: 600; color: var(--text-muted); white-space: nowrap;">{{ item.progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" :class="statusColors[item.status]">
                                    <span class="badge-dot" :class="'badge-dot--' + (item.status === 'active' ? 'success' : item.status === 'pending' ? 'warning' : 'info')" />
                                    {{ item.status }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn--ghost btn--icon btn--sm" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn btn--ghost btn--icon btn--sm btn-danger-ghost" title="Delete" @click="confirmDelete(item)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredClasses.length === 0" class="empty-state">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
                <h3 class="empty-title">No classes found</h3>
                <p class="empty-desc">{{ searchQuery ? 'No classes match your search criteria.' : 'No classes have been created yet. Create your first class to get started.' }}</p>
                <button v-if="!searchQuery" class="btn btn--primary" @click="openAdd">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Class
                </button>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-info">
                    Showing <strong>{{ filteredClasses.length }}</strong> of <strong>{{ classes.length }}</strong> classes
                </div>
            </div>
        </div>

        <!-- Create Class Dialog -->
        <div v-if="showAddDialog" class="dialog-overlay anim-fade-in" @click.self="showAddDialog = false">
            <div class="dialog">
                <div class="dialog-header">
                    <h3 class="dialog-title">Create New Class</h3>
                    <p class="dialog-desc">Set up a new class with teacher assignment and schedule.</p>
                </div>
                <div class="dialog-body">
                    <div class="form-group">
                        <label class="form-label">Class Name</label>
                        <input v-model="newClass.name" type="text" class="form-input" placeholder="e.g. Mathematics 101" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">Grade</label>
                            <select v-model="newClass.grade" class="form-input form-select">
                                <option v-for="g in grades" :key="g" :value="g">{{ g }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Section</label>
                            <select v-model="newClass.section" class="form-input form-select">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teacher</label>
                        <input v-model="newClass.teacher" type="text" class="form-input" placeholder="e.g. Chea Vannak" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">School</label>
                        <input v-model="newClass.school" type="text" class="form-input" placeholder="e.g. Phnom Penh High School" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Schedule</label>
                        <input v-model="newClass.schedule" type="text" class="form-input" placeholder="e.g. Mon, Wed, Fri 8:00 AM" />
                        <p class="form-hint">Specify days and times for the class sessions.</p>
                    </div>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showAddDialog = false">Cancel</button>
                    <button class="btn btn--primary" :disabled="!newClass.name || !newClass.teacher" @click="addClass">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Create Class
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div v-if="showDeleteDialog && selectedClass" class="dialog-overlay anim-fade-in" @click.self="showDeleteDialog = false">
            <div class="dialog" style="max-width: 400px;">
                <div class="dialog-header">
                    <h3 class="dialog-title">Delete Class</h3>
                    <p class="dialog-desc">
                        Are you sure you want to delete <strong>{{ selectedClass.name }}</strong> (Section {{ selectedClass.section }})?
                        This will remove all associated data.
                    </p>
                </div>
                <div class="dialog-footer">
                    <button class="btn btn--outline" @click="showDeleteDialog = false">Cancel</button>
                    <button class="btn btn--danger" @click="executeDelete">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete Class
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
