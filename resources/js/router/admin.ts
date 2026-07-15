import { createRouter, createMemoryHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: () => import('@/pages/admin/AdminDashboardShell.vue'),
        children: [
            {
                path: '',
                name: 'admin.dashboard.overview',
                component: () => import('@/pages/admin/DashboardOverview.vue'),
            },
            {
                path: 'users',
                name: 'admin.users',
                component: () => import('@/pages/admin/users/Index.vue'),
            },
            {
                path: 'schools',
                name: 'admin.schools',
                component: () => import('@/pages/admin/schools/Index.vue'),
            },
            {
                path: 'classes',
                name: 'admin.classes',
                component: () => import('@/pages/admin/classes/Index.vue'),
            },
            {
                path: 'reports',
                name: 'admin.reports',
                component: () => import('@/pages/admin/reports/Index.vue'),
            },
            {
                path: 'settings',
                name: 'admin.settings',
                component: () => import('@/pages/admin/settings/Index.vue'),
            },
        ],
    },
]

const adminRouter = createRouter({
    history: createMemoryHistory(),
    routes,
})

export default adminRouter
