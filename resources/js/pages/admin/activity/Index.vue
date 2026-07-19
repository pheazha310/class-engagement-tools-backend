<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

interface ActivityItem {
    id: number
    user: string
    initials: string
    avatarColor: string
    action: string
    target: string
    timestamp: string
    type: 'registration' | 'school' | 'system' | 'quiz' | 'poll'
    details?: string
    school?: string
    teacher?: string
    participants?: number
    status?: 'completed' | 'pending' | 'active'
}

const activities = ref<ActivityItem[]>([
    { id: 1, user: 'Sok Chan', initials: 'SC', avatarColor: '#4f46e5', action: 'registered', target: 'as a new student', timestamp: '2 minutes ago', type: 'registration', details: 'New student account created with email verification pending.', school: 'Phnom Penh High School', status: 'completed' },
    { id: 2, user: 'Hun Kim', initials: 'HK', avatarColor: '#10b981', action: 'created', target: 'school: "Phnom Penh High School"', timestamp: '15 minutes ago', type: 'school', details: 'New educational institution registered in the system.', teacher: 'Mr. Hun Kim', status: 'completed' },
    { id: 3, user: 'Chea Vannak', initials: 'CV', avatarColor: '#f59e0b', action: 'started', target: 'class: "Mathematics 101"', timestamp: '32 minutes ago', type: 'poll', details: 'New poll session created with 5 questions for 30 students.', school: 'Phnom Penh High School', participants: 30, status: 'active' },
    { id: 4, user: 'Srey Mom', initials: 'SM', avatarColor: '#3b82f6', action: 'submitted', target: 'quiz: "Science Midterm"', timestamp: '1 hour ago', type: 'quiz', details: 'Quiz submission auto-graded with a score of 85%.', teacher: 'Ms. Srey Mom', participants: 22, status: 'completed' },
    { id: 5, user: 'Vann Sovann', initials: 'VS', avatarColor: '#ef4444', action: 'registered', target: 'as a new teacher', timestamp: '2 hours ago', type: 'registration', details: 'Teacher account created with Mathematics specialization.', school: 'Sisowath High School', status: 'completed' },
    { id: 6, user: 'Meas Bora', initials: 'MB', avatarColor: '#8b5cf6', action: 'created', target: 'student accounts for exam preparation', timestamp: '3 hours ago', type: 'school', details: 'Bulk import of 45 student accounts for upcoming exams.', teacher: 'Mr. Meas Bora', participants: 45, status: 'completed' },
    { id: 7, user: 'Dara Pheaktra', initials: 'DP', avatarColor: '#14b8a6', action: 'completed', target: 'wheel spin: "Class 6A Selection"', timestamp: '4 hours ago', type: 'system', details: 'Random name wheel spin completed with 28 participants.', school: 'Sisowath High School', participants: 28, status: 'completed' },
    { id: 8, user: 'Ratanak Vireak', initials: 'RV', avatarColor: '#f97316', action: 'submitted', target: 'quiz: "Khmer Literature"', timestamp: '5 hours ago', type: 'quiz', details: 'Quiz submission with 18 participants. Average score: 72%.', teacher: 'Mr. Ratanak Vireak', participants: 18, status: 'completed' },
    { id: 9, user: 'Sokha Makara', initials: 'SM', avatarColor: '#e11d48', action: 'registered', target: 'as a new student', timestamp: '6 hours ago', type: 'registration', details: 'Student account created for grade 10 section B.', school: 'Bak Touk High School', status: 'pending' },
    { id: 10, user: 'Kosal Bunnarong', initials: 'KB', avatarColor: '#0ea5e9', action: 'started', target: 'poll: "History Review Session 3"', timestamp: '7 hours ago', type: 'poll', details: 'Live polling session started with real-time results.', teacher: 'Mr. Kosal Bunnarong', participants: 25, status: 'active' },
])

const searchQuery = ref('')
const filterType = ref<string>('all')
const filterStatus = ref<string>('all')
const selectedActivity = ref<ActivityItem | null>(null)
const detailDialogOpen = ref(false)

const filteredActivities = computed(() => {
    return activities.value.filter(a => {
        const matchesSearch = !searchQuery.value ||
            a.user.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            a.target.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesType = filterType.value === 'all' || a.type === filterType.value

        return matchesSearch && matchesType
    })
})

function showDetail(activity: ActivityItem) {
    selectedActivity.value = activity
    detailDialogOpen.value = true
}

const typeColors: Record<string, string> = {
    registration: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    school: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    quiz: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    poll: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    system: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
}

const typeIcons: Record<string, string> = {
    registration: `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>`,
    school: `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`,
    quiz: `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>`,
    poll: `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
    system: `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>`,
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Activity Log</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor all platform activities, registrations, and system events</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" class="gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Refresh
                </Button>
                <Button variant="outline" size="sm" class="gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </Button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-6">
            <div class="relative flex-1 max-w-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by user or activity..."
                    class="pl-10"
                />
            </div>
            <div class="flex gap-2">
                <select
                    v-model="filterType"
                    class="h-9 px-3 text-sm bg-white border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                >
                    <option value="all">All Types</option>
                    <option value="registration">Registration</option>
                    <option value="school">School</option>
                    <option value="quiz">Quiz</option>
                    <option value="poll">Poll</option>
                    <option value="system">System</option>
                </select>
            </div>
        </div>

        <!-- Activity Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ activities.length }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Activities</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ activities.filter(a => a.type === 'registration').length }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Registrations</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ activities.filter(a => a.type === 'school' || a.type === 'quiz').length }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Content Created</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ activities.filter(a => a.status === 'active').length }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Currently Active</div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <div
                    v-for="activity in filteredActivities"
                    :key="activity.id"
                    class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                    @click="showDetail(activity)"
                >
                    <!-- Avatar -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full text-xs font-bold text-white shrink-0 ring-2 ring-white dark:ring-gray-800" :style="{ background: activity.avatarColor }">
                        {{ activity.initials }}
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-snug">
                                <strong class="font-semibold text-gray-900 dark:text-white">{{ activity.user }}</strong>
                                {{ activity.action }}
                                {{ activity.target }}
                            </p>
                            <Badge :class="typeColors[activity.type] || typeColors.system" class="text-[10px] px-1.5 py-0.5 capitalize shrink-0">
                                {{ activity.type }}
                            </Badge>
                        </div>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ activity.timestamp }}
                            </span>
                            <span v-if="activity.school" class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                {{ activity.school }}
                            </span>
                            <span v-if="activity.participants" class="text-xs text-gray-400 dark:text-gray-500">
                                {{ activity.participants }} participants
                            </span>
                        </div>
                    </div>

                    <!-- Arrow -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-300 dark:text-gray-600 shrink-0 mt-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>

                <!-- Empty state -->
                <div v-if="filteredActivities.length === 0" class="flex flex-col items-center justify-center py-12 px-4">
                    <div class="w-14 h-14 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-7 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No activities found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-xs">Try adjusting your search or filter to find what you&rsquo;re looking for.</p>
                </div>
            </div>
        </div>

        <!-- Detail Dialog -->
        <Dialog v-model:open="detailDialogOpen">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <span>Activity Details</span>
                        <Badge v-if="selectedActivity" :class="typeColors[selectedActivity.type] || typeColors.system" class="text-xs capitalize">{{ selectedActivity.type }}</Badge>
                    </DialogTitle>
                    <DialogDescription>Detailed information about this activity</DialogDescription>
                </DialogHeader>

                <div v-if="selectedActivity" class="space-y-4">
                    <!-- User info -->
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full text-xs font-bold text-white shrink-0 ring-2 ring-white dark:ring-gray-800" :style="{ background: selectedActivity.avatarColor }">
                            {{ selectedActivity.initials }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedActivity.user }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ selectedActivity.action }} {{ selectedActivity.target }}</p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Time</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedActivity.timestamp }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Type</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ selectedActivity.type }}</p>
                        </div>
                        <div v-if="selectedActivity.school" class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">School</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedActivity.school }}</p>
                        </div>
                        <div v-if="selectedActivity.participants" class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Participants</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedActivity.participants }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedActivity.details" class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Description</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ selectedActivity.details }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
