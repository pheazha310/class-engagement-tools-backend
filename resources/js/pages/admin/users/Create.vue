<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { get, post } from '@/services/api'

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
const errors = ref<Record<string, string>>({})

onMounted(async () => {
    const res = await get<string[]>('/api/admin/roles')
    if (res.data) availableRoles.value = res.data
})

function toggleRole(role: string) {
    const idx = form.value.roles.indexOf(role)
    if (idx === -1) form.value.roles.push(role)
    else form.value.roles.splice(idx, 1)
}

async function submit() {
    submitting.value = true
    errors.value = {}
    const res = await post('/api/admin/users', form.value)
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new user to the platform</p>
        </div>

        <div class="max-w-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <form @submit.prevent="submit" class="space-y-5">
                <div v-if="errors.general" class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                    {{ errors.general }}
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="form.name" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                    <p v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</p>
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input v-model="form.email" type="email" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                    <p v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</p>
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input v-model="form.password" type="password" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" required class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900" />
                </div>

                <div class="grid gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="role in availableRoles" :key="role" class="flex items-center gap-2 text-sm cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="form.roles.includes(role)"
                                @change="toggleRole(role)"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
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
                        Create User
                    </button>
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors" @click="router.push('/admin/dashboard/users')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
