<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { get, post } from '@/services/api'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'

interface RoleOption {
    id: number
    name: string
    users_count: number
    permissions: string[]
    is_protected: boolean
}

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
const errors = ref<Record<string, string>>({})
const showPassword = ref(false)
const showConfirmPassword = ref(false)

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

onMounted(async () => {
    const res = await get<RoleOption[]>('/api/admin/roles')
    if (res.data) availableRoles.value = res.data
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
    const res = await post('/api/admin/users', form.value)

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

    toast.success('User created successfully', {
        description: `${form.value.name} has been added to the platform.`,
    })
    router.push('/admin/dashboard/users')
}

function cancel() {
    router.push('/admin/dashboard/users')
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
                <span class="text-gray-700 dark:text-gray-300 font-medium">Create User</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new user to the platform with roles and profile information</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <form @submit.prevent="submit">
                <!-- General Information -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">General Information</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Basic account details for the new user</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Name -->
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <Input
                                v-model="form.name"
                                placeholder="John Doe"
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
                                placeholder="john@example.com"
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
                            <p v-if="errors.roles" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ errors.roles }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Password</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Set the user's initial password</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password <span class="text-red-500">*</span>
                            </label>
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
                            <!-- Password strength bar -->
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
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
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
                    <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-xs text-amber-500 mt-2 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Passwords do not match
                    </p>
                </div>

                <!-- Location & School -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Location & School</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Optional profile information about the user's location</p>

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
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        {{ submitting ? 'Creating...' : 'Create User' }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- General API error -->
        <div v-if="errors.general" class="mt-4 flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-600 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ errors.general }}
        </div>
    </div>
</template>
