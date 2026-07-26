<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { get, put } from '@/services/api'

const route = useRoute()
const router = useRouter()
const userId = route.params.id as string

const loading = ref(true)
const submitting = ref(false)

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
})

const roleOptions = [
    { value: 'student', label: 'Student' },
    { value: 'teacher', label: 'Teacher' },
    { value: 'admin', label: 'Admin' },
]

async function fetchUser() {
    loading.value = true
    const response = await get<{ data: any }>(`/api/admin/users/${userId}`)
    if (response.data) {
        form.value.name = response.data.data.name
        form.value.email = response.data.data.email
        form.value.role = response.data.data.role
    } else if (response.error) {
        toast.error(response.error.message || 'Failed to load user')
        router.push('/admin/dashboard/users')
    }
    loading.value = false
}

async function handleSubmit() {
    submitting.value = true

    const payload: Record<string, any> = {
        name: form.value.name,
        email: form.value.email,
        role: form.value.role,
    }
    if (form.value.password) {
        payload.password = form.value.password
        payload.password_confirmation = form.value.password_confirmation
    }

    const response = await put(`/api/admin/users/${userId}`, payload)
    submitting.value = false

    if (!response.error) {
        toast.success('User updated successfully')
        router.push('/admin/dashboard/users')
    } else {
        toast.error(response.error.message || 'Failed to update user')
    }
}

onMounted(fetchUser)
</script>

<template>
    <div>
        <div class="mb-6">
            <button
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4 transition-colors cursor-pointer"
                @click="router.push('/admin/dashboard/users')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Users
            </button>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update user information and role</p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-16">
            <div class="size-8 border-2 border-blue-600/30 border-t-blue-600 rounded-full animate-spin" />
        </div>

        <div v-else class="max-w-2xl bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="handleSubmit" class="space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 dark:text-gray-200 transition-all"
                    />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 dark:text-gray-200 transition-all"
                    />
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role</label>
                    <div class="flex gap-2">
                        <button
                            v-for="opt in roleOptions"
                            :key="opt.value"
                            type="button"
                            class="px-4 py-2 text-sm font-medium rounded-lg border transition-all cursor-pointer"
                            :class="form.role === opt.value
                                ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700'"
                            @click="form.role = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Password (optional for edit) -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Leave blank to keep current password</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                minlength="8"
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 dark:text-gray-200 transition-all"
                                placeholder="Leave blank to keep current"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm New Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 dark:text-gray-200 transition-all"
                                placeholder="Repeat new password"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors cursor-pointer shadow-sm"
                    >
                        <div v-if="submitting" class="size-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        {{ submitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button
                        type="button"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                        @click="router.push('/admin/dashboard/users')"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
