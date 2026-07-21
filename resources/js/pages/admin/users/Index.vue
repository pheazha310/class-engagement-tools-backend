<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { del, get } from '@/services/api'

import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'

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
const deleteTarget = ref<User | null>(null)
const deleteDialogOpen = ref(false)
const deleting = ref(false)

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

function confirmDelete(user: User) {
    deleteTarget.value = user
    deleteDialogOpen.value = true
}

async function executeDelete() {
    if (!deleteTarget.value) return
    deleting.value = true
    const response = await del(`/api/admin/users/${deleteTarget.value.id}`)
    deleting.value = false
    deleteDialogOpen.value = false

    if (!response.error) {
        toast.success(`User "${deleteTarget.value.name}" has been deleted`)
        deleteTarget.value = null
        fetchUsers(pagination.value.current_page)
    } else {
        toast.error(response.error.message || 'Failed to delete user')
    }
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
}

function getAvatarColor(name: string): string {
    const colors = [
        'bg-blue-500', 'bg-emerald-500', 'bg-violet-500', 'bg-amber-500',
        'bg-rose-500', 'bg-cyan-500', 'bg-pink-500', 'bg-indigo-500',
        'bg-teal-500', 'bg-orange-500',
    ]
    let hash = 0
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash)
    }
    return colors[Math.abs(hash) % colors.length]
}

function getRoleBadgeVariant(role: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (role) {
        case 'admin': return 'destructive'
        case 'teacher': return 'default'
        case 'student': return 'secondary'
        default: return 'outline'
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

function navigateToEdit(user: User) {
    router.push(`/admin/dashboard/users/${user.id}/edit`)
}

function sanitizeLabel(label: string): string {
    const cleaned = label
        .replace('&laquo;', '‹')
        .replace('&raquo;', '›')
        .replace(/&[a-z]+;/g, '')
        .trim()
    // If cleaning removed everything (e.g. empty separator), show the raw directional arrow
    if (!cleaned) {
        const match = label.match(/&laquo;|&raquo;/)
        return match ? (match[0] === '&laquo;' ? '‹' : '›') : cleaned
    }
    return cleaned
}

function handlePageClick(link: { url: string | null; label: string; active: boolean }) {
    if (!link.url) return
    try {
        const url = new URL(link.url)
        const page = url.searchParams.get('page')
        if (page) {
            fetchUsers(Number(page))
        }
    } catch {
        // Invalid URL, ignore
    }
}

onMounted(() => fetchUsers())
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    User Management
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage registered users, their roles, and permissions
                </p>
            </div>
            <Button
                variant="default"
                size="default"
                class="gap-2 cursor-pointer"
                @click="router.push('/admin/dashboard/users/create')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Add User
            </Button>
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
                v-model="search"
                type="text"
                placeholder="Search by name or email..."
                class="w-full h-9 pl-10 pr-4 text-sm bg-white border border-gray-200 rounded-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all"
            />
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-2">
            <div
                v-for="i in 5"
                :key="i"
                class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
            >
                <Skeleton class="size-10 rounded-full shrink-0" />
                <div class="flex-1 space-y-2">
                    <Skeleton class="h-4 w-40" />
                    <Skeleton class="h-3 w-56" />
                </div>
                <div class="hidden md:block space-y-2">
                    <Skeleton class="h-4 w-20" />
                    <Skeleton class="h-3 w-16" />
                </div>
                <Skeleton class="size-8 rounded-md shrink-0" />
            </div>
        </div>

        <!-- Users Table -->
        <div
            v-else-if="users.length > 0"
            class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80">
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">User</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden sm:table-cell">Roles</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden lg:table-cell">School / Location</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden md:table-cell">Status</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden xl:table-cell">Joined</th>
                            <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="group transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/20 cursor-pointer"
                            @click="navigateToEdit(user)"
                        >
                            <!-- User -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-9 shrink-0 ring-2 ring-white dark:ring-gray-800">
                                        <AvatarFallback :class="getAvatarColor(user.name)" class="text-white text-xs font-semibold">
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[180px] sm:max-w-[220px]">
                                            {{ user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[180px] sm:max-w-[220px]">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Roles -->
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role"
                                        :variant="getRoleBadgeVariant(role)"
                                        class="text-xs capitalize"
                                    >
                                        {{ role }}
                                    </Badge>
                                    <span v-if="user.roles.length === 0" class="text-xs text-gray-400 italic">No roles</span>
                                </div>
                            </td>

                            <!-- School / Location -->
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <div v-if="user.school_name && user.school_name !== '-'" class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ user.school_name }}
                                </div>
                                <div v-else class="text-sm text-gray-400 italic">-</div>
                                <div v-if="user.country_name && user.country_name !== '-'" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ user.country_name }}{{ user.province_name && user.province_name !== '-' ? ' / ' + user.province_name : '' }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="inline-block w-2 h-2 rounded-full"
                                        :class="user.email_verified_at
                                            ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.4)]'
                                            : 'bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.4)]'"
                                    />
                                    <span class="text-xs" :class="user.email_verified_at ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Joined -->
                            <td class="px-4 py-3.5 hidden xl:table-cell">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(user.created_at) }}</span>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5" @click.stop>
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-8 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                        title="Edit user"
                                        @click="navigateToEdit(user)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-8 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        title="Delete user"
                                        @click="confirmDelete(user)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50"
            >
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Showing <strong>{{ pagination.from }}</strong> – <strong>{{ pagination.to }}</strong> of <strong>{{ pagination.total }}</strong> users
                </span>
                <div class="flex gap-1">
                    <button
                        v-for="link in pagination.links"
                        :key="link.label"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-all cursor-pointer"
                        :class="link.active
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        v-html="sanitizeLabel(link.label)"
                        :disabled="!link.url"
                        @click="handlePageClick(link)"
                    />
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-16 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">No users found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center max-w-xs">
                {{ search ? 'No users match your search criteria. Try a different search term.' : 'There are no registered users yet. Get started by adding the first user.' }}
            </p>
            <Button v-if="!search" variant="default" size="sm" class="gap-2 cursor-pointer" @click="router.push('/admin/dashboard/users/create')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add User
            </Button>
        </div>

        <!-- Delete confirmation dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong class="text-foreground">{{ deleteTarget?.name }}</strong>?
                        This action cannot be undone. The user will be permanently removed from the system.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <DialogClose as-child>
                        <Button variant="outline" class="cursor-pointer">Cancel</Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        class="gap-2 cursor-pointer"
                        :disabled="deleting"
                        @click="executeDelete"
                    >
                        <div v-if="deleting" class="size-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        {{ deleting ? 'Deleting...' : 'Delete User' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
