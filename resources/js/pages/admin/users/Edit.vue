<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { get, put } from '@/services/api'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Skeleton } from '@/components/ui/skeleton'

interface RoleOption {
    id: number
    name: string
    users_count: number
    permissions: string[]
    is_protected: boolean
}

interface UserData {
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

const route = useRoute()
const router = useRouter()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as string[],
    school_name: '',
    country_name: '',
    province_name: '',
})

const availableRoles = ref<RoleOption[]>([])
const submitting = ref(false)
const loading = ref(true)
const errors = ref<Record<string, string>>({})
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const changePassword = ref(false)
const userInfo = ref<UserData | null>(null)

// Password strength indicator
const passwordStrength = computed(() => {
    const pw = form.value.password
    if (!pw) return { level: 0, label: '', color: '', bg: '' }

    let score = 0
    if (pw.length >= 8) score++
    if (pw.length >= 12) score++
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++
    if (/\d/.test(pw)) score++
    if (/[^a-zA-Z0-9]/.test(pw)) score++

    if (score <= 1) return { level: 1, label: 'Weak', color: 'text-red-500', bg: 'bg-red-500' }
    if (score <= 2) return { level: 2, label: 'Fair', color: 'text-orange-500', bg: 'bg-orange-500' }
    if (score <= 3) return { level: 3, label: 'Good', color: 'text-yellow-500', bg: 'bg-yellow-500' }
    if (score <= 4) return { level: 4, label: 'Strong', color: 'text-lime-500', bg: 'bg-lime-500' }
    return { level: 5, label: 'Very Strong', color: 'text-emerald-500', bg: 'bg-emerald-500' }
})

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
    ]
    let hash = 0
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash)
    }
    return colors[Math.abs(hash) % colors.length]
}

onMounted(async () => {
    const [userRes, rolesRes] = await Promise.all([
        get<UserData>(`/api/admin/users/${route.params.id}`),
        get<RoleOption[]>('/api/admin/roles'),
    ])

    if (rolesRes.data) availableRoles.value = rolesRes.data

    if (userRes.data) {
        userInfo.value = userRes.data
        form.value = {
            name: userRes.data.name,
            email: userRes.data.email,
            password: '',
            password_confirmation: '',
            roles: userRes.data.roles || [],
            school_name: userRes.data.school_name === '-' ? '' : userRes.data.school_name,
            country_name: userRes.data.country_name === '-' ? '' : userRes.data.country_name,
            province_name: userRes.data.province_name === '-' ? '' : userRes.data.province_name,
        }
    }
    loading.value = false
})

function toggleRole(roleName: string) {
    const idx = form.value.roles.indexOf(roleName)
    if (idx === -1) form.value.roles.push(roleName)
    else form.value.roles.splice(idx, 1)
}

function roleBadgeVariant(roleName: string, isSelected: boolean): 'default' | 'outline' | 'secondary' | 'destructive' {
    if (!isSelected) return 'outline'
    switch (roleName) {
        case 'admin': return 'destructive'
        case 'teacher': return 'default'
        case 'student': return 'secondary'
        default: return 'default'
    }
}

async function submit() {
    submitting.value = true
    errors.value = {}

    const body: Record<string, any> = {
        name: form.value.name,
        email: form.value.email,
        roles: form.value.roles,
        school_name: form.value.school_name,
        country_name: form.value.country_name,
        province_name: form.value.province_name,
    }
    if (changePassword.value && form.value.password) {
        body.password = form.value.password
        body.password_confirmation = form.value.password_confirmation
    }

    const res = await put(`/api/admin/users/${route.params.id}`, body)
    if (res.error) {
        try {
            const parsed = JSON.parse(res.error)
            if (typeof parsed === 'object' && parsed !== null) {
                errors.value = parsed
            } else {
                errors.value = { general: res.error }
            }
        } catch {
            errors.value = { general: res.error }
        }
        submitting.value = false
        return
    }

    toast.success('User updated successfully', {
        description: `${form.value.name}'s account has been updated.`,
    })
    router.push('/admin/dashboard/users')
}

function cancel() {
    router.push('/admin/dashboard/users')
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}
</script>

<template>
    <div class="max-w-3xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <button class="hover:text-gray-700 dark:hover:text-gray-300 transition-colors cursor-pointer" @click="cancel">
                    User Management
                </button>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-gray-700 dark:text-gray-300 font-medium">Edit User</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update user details, roles, and profile information</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-6">
                    <Skeleton class="size-14 rounded-full" />
                    <div class="space-y-2">
                        <Skeleton class="h-5 w-40" />
                        <Skeleton class="h-4 w-56" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div v-for="i in 4" :key="i" class="space-y-2">
                        <Skeleton class="h-4 w-20" />
                        <Skeleton class="h-9 w-full" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div v-else class="space-y-6">
            <!-- User Info Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <Avatar class="size-14 ring-2 ring-white dark:ring-gray-800">
                        <AvatarFallback :class="getAvatarColor(userInfo?.name || '')" class="text-white text-lg font-semibold">
                            {{ getInitials(userInfo?.name || '') }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ userInfo?.name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ userInfo?.email }}</p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="inline-block w-2 h-2 rounded-full"
                                    :class="userInfo?.email_verified_at
                                        ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.4)]'
                                        : 'bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.4)]'"
                                />
                                <span class="text-xs" :class="userInfo?.email_verified_at ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                                    {{ userInfo?.email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Joined {{ formatDate(userInfo?.created_at || null) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <form @submit.prevent="submit">
                    <!-- General Information -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">General Information</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Basic account details</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <Input
                                    v-model="form.name"
                                    :class="errors.name ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                />
                                <p v-if="errors.name" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <Input
                                    v-model="form.email"
                                    type="email"
                                    :class="errors.email ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                />
                                <p v-if="errors.email" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    {{ errors.email }}
                                </p>
                            </div>

                            <!-- Role Selection -->
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <button
                                        v-for="role in availableRoles"
                                        :key="role.id"
                                        type="button"
                                        class="cursor-pointer transition-all"
                                        @click="toggleRole(role.name)"
                                    >
                                        <Badge
                                            :variant="roleBadgeVariant(role.name, form.roles.includes(role.name))"
                                            class="text-xs capitalize px-2.5 py-1"
                                        >
                                            <svg
                                                v-if="form.roles.includes(role.name)"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="size-3 mr-0.5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                            >
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            {{ role.name }}
                                        </Badge>
                                    </button>
                                    <span v-if="availableRoles.length === 0" class="text-xs text-gray-400 italic">Loading roles...</span>
                                </div>
                                <p v-if="errors.roles" class="text-xs text-red-500 mt-1">{{ errors.roles }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Password</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update the user's password if needed</p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="cursor-pointer"
                                @click="changePassword = !changePassword"
                            >
                                <svg v-if="!changePassword" xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                {{ changePassword ? 'Cancel' : 'Change Password' }}
                            </Button>
                        </div>

                        <template v-if="changePassword">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Password -->
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                    <div class="relative">
                                        <Input
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            placeholder="Min. 8 characters"
                                            :class="errors.password ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer transition-colors"
                                            @click="showPassword = !showPassword"
                                        >
                                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                        </button>
                                    </div>
                                    <!-- Password strength -->
                                    <div v-if="form.password" class="mt-2">
                                        <div class="flex gap-1 mb-1">
                                            <div
                                                v-for="i in 5"
                                                :key="i"
                                                class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                                :class="i <= passwordStrength.level ? passwordStrength.bg : 'bg-gray-200 dark:bg-gray-700'"
                                            />
                                        </div>
                                        <p class="text-xs" :class="passwordStrength.color">{{ passwordStrength.label }}</p>
                                    </div>
                                    <p v-if="errors.password" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        {{ errors.password }}
                                    </p>
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                                    <div class="relative">
                                        <Input
                                            v-model="form.password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            placeholder="Re-enter password"
                                            :class="errors.password ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer transition-colors"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                        >
                                            <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Password mismatch hint -->
                            <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-xs text-amber-500 mt-3 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                Passwords do not match
                            </p>
                        </template>

                        <p v-if="!changePassword" class="text-xs text-gray-400 dark:text-gray-500 italic">
                            Leave as-is to keep the current password. Click "Change Password" to set a new one.
                        </p>
                    </div>

                    <!-- Location & School -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Location & School</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Profile location information</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                                <Input
                                    v-model="form.country_name"
                                    placeholder="e.g. Cambodia"
                                    :class="errors.country_name ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                />
                                <p v-if="errors.country_name" class="text-xs text-red-500 mt-1">{{ errors.country_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Province</label>
                                <Input
                                    v-model="form.province_name"
                                    placeholder="e.g. Phnom Penh"
                                    :class="errors.province_name ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                />
                                <p v-if="errors.province_name" class="text-xs text-red-500 mt-1">{{ errors.province_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                                <Input
                                    v-model="form.school_name"
                                    placeholder="e.g. High School"
                                    :class="errors.school_name ? 'border-red-500 focus-visible:ring-red-500/30' : ''"
                                />
                                <p v-if="errors.school_name" class="text-xs text-red-500 mt-1">{{ errors.school_name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-end gap-3">
                        <Button type="button" variant="outline" class="cursor-pointer" @click="cancel">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="submitting" class="gap-2 cursor-pointer">
                            <div v-if="submitting" class="size-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            {{ submitting ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- General API error -->
            <div v-if="errors.general" class="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-600 dark:text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errors.general }}
            </div>
        </div>
    </div>
</template>
