<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    permissions: string[]
    role?: {
        id: number
        name: string
        permissions: string[]
        is_protected: boolean
    }
    submitLabel: string
}>()

const emit = defineEmits<{
    submit: [data: { name: string; permissions: string[] }]
    cancel: []
}>()

const name = ref(props.role?.name ?? '')
const selectedPermissions = ref<string[]>(props.role?.permissions ?? [])

function togglePermission(permission: string) {
    const idx = selectedPermissions.value.indexOf(permission)
    if (idx === -1) selectedPermissions.value.push(permission)
    else selectedPermissions.value.splice(idx, 1)
}

function submit() {
    emit('submit', { name: name.value, permissions: selectedPermissions.value })
}
</script>

<template>
    <div class="max-w-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Role name</label>
                <input
                    v-model="name"
                    required
                    :disabled="role?.is_protected"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 disabled:opacity-50"
                />
                <p v-if="role?.is_protected" class="text-xs text-gray-400">This is a built-in role; its name cannot be changed.</p>
            </div>

            <div class="grid gap-3">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label
                        v-for="permission in permissions"
                        :key="permission"
                        class="flex items-center gap-2 text-sm cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedPermissions.includes(permission)"
                            @change="togglePermission(permission)"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-gray-700 dark:text-gray-300">{{ permission }}</span>
                    </label>
                    <p v-if="permissions.length === 0" class="text-sm text-gray-400">No permissions defined yet.</p>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ submitLabel }}
                </button>
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors" @click="emit('cancel')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>
