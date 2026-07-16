<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { get, put } from '@/services/api'

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

const availableRoles = ref<string[]>([])
const submitting = ref(false)
const loading = ref(true)
const errors = ref<Record<string, string>>({})

onMounted(async () => {
    const [userRes, rolesRes] = await Promise.all([
        get<any>(`/api/admin/users/${route.params.id}`),
        get<string[]>('/api/admin/roles'),
    ])

    if (rolesRes.data) availableRoles.value = rolesRes.data

    if (userRes.data) {
        form.value = {
            name: userRes.data.name,
            email: userRes.data.email,
            password: '',
            password_confirmation: '',
            roles: userRes.data.roles || [],
            school_name: userRes.data.school_name,
            country_name: userRes.data.country_name,
            province_name: userRes.data.province_name,
        }
    }
    loading.value = false
})

function toggleRole(role: string) {
    const idx = form.value.roles.indexOf(role)
    if (idx === -1) form.value.roles.push(role)
    else form.value.roles.splice(idx, 1)
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
    if (form.value.password) {
        body.password = form.value.password
        body.password_confirmation = form.value.password_confirmation
    }

    const res = await put(`/api/admin/users/${route.params.id}`, body)
    if (res.error) {
        try { errors.value = JSON.parse(res.error) } catch { errors.value = { general: res.error } }
        submitting.value = false
        return
    }
    router.push('/admin/dashboard/users')
}
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update user details and roles</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading user data...
        </div>

        <div v-else class="max-w-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <form @submit.prevent="submit" class="space-y-5">
                <div v-if="errors.general" class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                    {{ errors.general }}
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="form.name" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input v-model="form.email" type="email" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password (leave blank to keep current)</label>
                    <input v-model="form.password" type="password" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    <input v-model="form.password_confirmation" type="password" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="role in availableRoles" :key="role" class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" :checked="form.roles.includes(role)" @change="toggleRole(role)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-gray-700 dark:text-gray-300 capitalize">{{ role }}</span>
                        </label>
                    </div>
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">School Name</label>
                    <input v-model="form.school_name" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                    <input v-model="form.country_name" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Province</label>
                    <input v-model="form.province_name" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <div v-if="submitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        Save Changes
                    </button>
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors" @click="router.push('/admin/dashboard/users')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
