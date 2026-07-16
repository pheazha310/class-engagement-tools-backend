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
                path: 'users/create',
                name: 'admin.users.create',
                component: () => import('@/pages/admin/users/Create.vue'),
            },
            {
                path: 'users/:id/edit',
                name: 'admin.users.edit',
                component: () => import('@/pages/admin/users/Edit.vue'),
                props: true,
            },
            {
                path: 'roles',
                name: 'admin.roles',
                component: () => import('@/pages/admin/roles/Index.vue'),
            },
            {
                path: 'roles/create',
                name: 'admin.roles.create',
                component: () => import('@/pages/admin/roles/Create.vue'),
            },
            {
                path: 'roles/:id/edit',
                name: 'admin.roles.edit',
                component: () => import('@/pages/admin/roles/Edit.vue'),
                props: true,
            },
            {
                path: 'school-names',
                name: 'admin.school-names',
                component: () => import('@/pages/admin/locations/Index.vue'),
            },
            {
                path: 'school-names/create',
                name: 'admin.school-names.create',
                component: () => import('@/pages/admin/locations/Create.vue'),
            },
            {
                path: 'school-names/:id/edit',
                name: 'admin.school-names.edit',
                component: () => import('@/pages/admin/locations/Edit.vue'),
                props: true,
            },
        ],
    },
]

const adminRouter = createRouter({
    history: createMemoryHistory(),
    routes,
})

export default adminRouter
