import { defineStore } from 'pinia'
import { ref } from 'vue'
import { get } from '@/services/api'

export interface StatCard {
    id: string
    title: string
    value: number
    description: string
    growth: number
    growthLabel: string
    accent: 'primary' | 'success' | 'warning' | 'info'
    icon: string
}

export interface ChartDataset {
    label: string
    data: number[]
    borderColor: string
    backgroundColor: string
    fill: boolean
    tension: number
}

export interface ChartData {
    labels: string[]
    datasets: ChartDataset[]
}

export interface RecentActivity {
    id: number
    user: string
    initials: string
    avatarColor: string
    action: string
    target: string
    timestamp: string
    type: 'registration' | 'school' | 'system'
}

export interface Notification {
    id: number
    text: string
    time: string
    icon: string
    iconBg: string
}

export interface QuickAction {
    id: string
    label: string
    icon: string
    variant: 'primary' | 'success' | 'warning' | 'info'
    route: string
}

export interface DashboardStats {
    totalUsers: number
    totalSchools: number
    activeClasses: number
    todayActivities: number
    pendingUsers: number
    pendingSchools: number
}

export interface CurrentUser {
    name: string
    email: string
    role: string
    initials: string
    avatar: string | null
}

export interface DashboardApiResponse {
    stats: StatCard[]
    userRegistrationData: ChartData
    platformActivityData: ChartData
    recentActivities: RecentActivity[]
    notifications: Notification[]
    currentUser: CurrentUser
}

// ---- Mock data defaults (used when API is unavailable) ----
const mockStats: StatCard[] = [
    { id: 'total-users', title: 'Total Users', value: 2843, description: 'Registered platform users', growth: 12.5, growthLabel: 'vs last month', accent: 'primary', icon: 'users' },
    { id: 'total-schools', title: 'Total Schools', value: 48, description: 'Active educational institutions', growth: 8.2, growthLabel: 'vs last month', accent: 'success', icon: 'school' },
    { id: 'active-classes', title: 'Active Classes', value: 156, description: 'Ongoing classroom sessions', growth: -3.1, growthLabel: 'vs last month', accent: 'warning', icon: 'book' },
    { id: 'today-activities', title: "Today's Activities", value: 1274, description: 'Interactions recorded today', growth: 23.8, growthLabel: 'vs yesterday', accent: 'info', icon: 'activity' },
    { id: 'pending-users', title: 'Pending Users', value: 23, description: 'Awaiting approval', growth: -5.2, growthLabel: 'vs yesterday', accent: 'warning', icon: 'clock' },
    { id: 'pending-schools', title: 'Pending Schools', value: 7, description: 'New registrations', growth: 2.1, growthLabel: 'vs last week', accent: 'info', icon: 'building' },
]

const mockUserChart: ChartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        { label: 'Students', data: [320, 450, 380, 520, 610, 480, 390, 420, 560, 490, 530, 470], borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.08)', fill: true, tension: 0.4 },
        { label: 'Teachers', data: [45, 62, 55, 78, 82, 64, 48, 53, 71, 66, 74, 59], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.08)', fill: true, tension: 0.4 },
    ],
}

const mockActivityChart: ChartData = {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        { label: 'Page Views', data: [12400, 10800, 14200, 13500, 11800, 6200, 4500], borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.08)', fill: true, tension: 0.4 },
        { label: 'Unique Visitors', data: [6800, 5900, 7200, 6900, 6100, 3400, 2800], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.08)', fill: true, tension: 0.4 },
    ],
}

const mockActivities: RecentActivity[] = [
    { id: 1, user: 'Sok Chan', initials: 'SC', avatarColor: '#4f46e5', action: 'registered', target: 'as a new student', timestamp: '2 minutes ago', type: 'registration' },
    { id: 2, user: 'Hun Kim', initials: 'HK', avatarColor: '#10b981', action: 'created', target: 'a new school: "Phnom Penh High School"', timestamp: '15 minutes ago', type: 'school' },
    { id: 3, user: 'Chea Vannak', initials: 'CV', avatarColor: '#f59e0b', action: 'started', target: 'a new class: "Mathematics 101"', timestamp: '32 minutes ago', type: 'system' },
    { id: 4, user: 'Srey Mom', initials: 'SM', avatarColor: '#3b82f6', action: 'submitted', target: 'the quiz "Science Midterm"', timestamp: '1 hour ago', type: 'system' },
    { id: 5, user: 'Vann Sovann', initials: 'VS', avatarColor: '#ef4444', action: 'registered', target: 'as a new teacher', timestamp: '2 hours ago', type: 'registration' },
    { id: 6, user: 'Meas Bora', initials: 'MB', avatarColor: '#8b5cf6', action: 'created', target: 'a new student account for test preparation', timestamp: '3 hours ago', type: 'school' },
]

const mockNotifications: Notification[] = [
    { id: 1, text: '<strong>Sok Chan</strong> just registered as a new student', time: '2 min ago', icon: '👤', iconBg: '#eef2ff' },
    { id: 2, text: '<strong>Hun Kim</strong> created a new school', time: '15 min ago', icon: '🏫', iconBg: '#ecfdf5' },
    { id: 3, text: '<strong>System</strong> completed daily backup successfully', time: '30 min ago', icon: '✅', iconBg: '#eff6ff' },
    { id: 4, text: '<strong>3 new reports</strong> are ready for review', time: '1 hour ago', icon: '📊', iconBg: '#fffbeb' },
]

const mockQuickActions: QuickAction[] = [
    { id: 'add-user', label: 'Add User', icon: 'user-plus', variant: 'primary', route: '/admin/dashboard/users/create' },
    { id: 'add-school', label: 'Add School', icon: 'building', variant: 'success', route: '/admin/dashboard/schools/create' },
    { id: 'create-class', label: 'Create Class', icon: 'book-open', variant: 'warning', route: '/admin/dashboard/classes/create' },
    { id: 'view-reports', label: 'View Reports', icon: 'bar-chart', variant: 'info', route: '/admin/dashboard/reports' },
]

const mockCurrentUser: CurrentUser = {
    name: 'Admin User',
    email: 'admin@classengage.com',
    role: 'Super Admin',
    initials: 'AU',
    avatar: null,
}

// ---- API base URL ----
const API_BASE = '/api/admin'

export const useAdminDashboardStore = defineStore('adminDashboard', () => {
    // ---- State ----
    const statsCards = ref<StatCard[]>(mockStats)
    const dashboardStats = ref<DashboardStats>({
        totalUsers: 2843,
        totalSchools: 48,
        activeClasses: 156,
        todayActivities: 1274,
        pendingUsers: 23,
        pendingSchools: 7,
    })
    const userRegistrationData = ref<ChartData>(mockUserChart)
    const platformActivityData = ref<ChartData>(mockActivityChart)
    const recentActivities = ref<RecentActivity[]>(mockActivities)
    const notifications = ref<Notification[]>(mockNotifications)
    const quickActions = ref<QuickAction[]>(mockQuickActions)
    const currentUser = ref<CurrentUser>(mockCurrentUser)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // ---- Actions ----

    /**
     * Fetch all dashboard data from the API.
     * Falls back to mock data on failure.
     */
    async function fetchDashboardData(): Promise<void> {
        isLoading.value = true
        error.value = null

        try {
            const response = await get<DashboardApiResponse>(`${API_BASE}/dashboard`)

            if (response.error) {
                console.warn('[DashboardStore] API error, using mock data:', response.error)
                error.value = response.error.message || 'Failed to load dashboard data'
                applyMockData()
            } else if (response.data) {
                applyApiData(response.data)
            } else {
                applyMockData()
            }
        } catch (e: any) {
            console.warn('[DashboardStore] API fetch failed, using mock data:', e.message)
            error.value = e.message || 'Failed to fetch dashboard data'
            applyMockData()
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Apply data from the API response to the store.
     */
    /**
     * Track whether the store has been populated from server-side Inertia props.
     */
    const hydratedFromServer = ref(false)

    function applyApiData(data: Partial<DashboardApiResponse>): void {
        hydratedFromServer.value = true
        if (data.stats && data.stats.length > 0) {
            statsCards.value = data.stats
            dashboardStats.value = {
                totalUsers: data.stats.find(s => s.id === 'total-users')?.value ?? 0,
                totalSchools: data.stats.find(s => s.id === 'total-schools')?.value ?? 0,
                activeClasses: data.stats.find(s => s.id === 'active-classes')?.value ?? 0,
                todayActivities: data.stats.find(s => s.id === 'today-activities')?.value ?? 0,
                pendingUsers: data.stats.find(s => s.id === 'pending-users')?.value ?? 0,
                pendingSchools: data.stats.find(s => s.id === 'pending-schools')?.value ?? 0,
            }
        }

        if (data.userRegistrationData) {
            userRegistrationData.value = data.userRegistrationData
        }

        if (data.platformActivityData) {
            platformActivityData.value = data.platformActivityData
        }

        if (data.recentActivities && data.recentActivities.length > 0) {
            recentActivities.value = data.recentActivities
        }

        if (data.notifications && data.notifications.length > 0) {
            notifications.value = data.notifications
        }

        if (data.currentUser) {
            currentUser.value = data.currentUser
        }
    }

    /**
     * Fall back to mock data when the API is unavailable.
     */
    function applyMockData(): void {
        statsCards.value = mockStats
        dashboardStats.value = {
            totalUsers: mockStats[0].value,
            totalSchools: mockStats[1].value,
            activeClasses: mockStats[2].value,
            todayActivities: mockStats[3].value,
            pendingUsers: mockStats[4].value,
            pendingSchools: mockStats[5].value,
        }
        userRegistrationData.value = mockUserChart
        platformActivityData.value = mockActivityChart
        recentActivities.value = mockActivities
        notifications.value = mockNotifications
        quickActions.value = mockQuickActions
        currentUser.value = mockCurrentUser
    }

    /**
     * Update stats optimistically (useful for real-time updates).
     */
    function updateStats(data: Partial<DashboardStats>): void {
        if (data.totalUsers !== undefined) {
            const card = statsCards.value.find(s => s.id === 'total-users')
            if (card) card.value = data.totalUsers
        }
        if (data.totalSchools !== undefined) {
            const card = statsCards.value.find(s => s.id === 'total-schools')
            if (card) card.value = data.totalSchools
        }
        if (data.activeClasses !== undefined) {
            const card = statsCards.value.find(s => s.id === 'active-classes')
            if (card) card.value = data.activeClasses
        }
        if (data.todayActivities !== undefined) {
            const card = statsCards.value.find(s => s.id === 'today-activities')
            if (card) card.value = data.todayActivities
        }

        dashboardStats.value = { ...dashboardStats.value, ...data }
    }

    /**
     * Clear any stored error state.
     */
    function clearError(): void {
        error.value = null
    }

    /**
     * Force reload dashboard data from the server.
     */
    async function refresh(): Promise<void> {
        await fetchDashboardData()
    }

    return {
        // State
        statsCards,
        dashboardStats,
        userRegistrationData,
        platformActivityData,
        recentActivities,
        notifications,
        quickActions,
        currentUser,
        isLoading,
        error,
        hydratedFromServer,
        // Actions
        fetchDashboardData,
        applyApiData,
        applyMockData,
        updateStats,
        clearError,
        refresh,
    }
})
