<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { get, post } from '@/services/api'
import Form from '@/pages/admin/locations/Form.vue'

const router = useRouter()
const lookupData = ref<{ countries: string[]; provinces: { name: string; country: string | null }[] }>({ countries: [], provinces: [] })
const loading = ref(true)

onMounted(async () => {
    const res = await get<{ countries: string[]; provinces: { name: string; country: string | null }[] }>('/api/admin/locations/lookup/data')
    if (res.data) lookupData.value = res.data
    loading.value = false
})

async function onSubmit(data: { school_name: string; country: string; province: string }) {
    const res = await post('/api/admin/locations', data)
    if (!res.error) {
        router.push('/admin/dashboard/school-names')
    } else {
        alert(res.error)
    }
}
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add School Name</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register a new school name</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading lookup data...
        </div>

        <Form
            v-else
            :countries="lookupData.countries"
            :provinces="lookupData.provinces"
            submit-label="Create School Name"
            @submit="onSubmit"
            @cancel="router.push('/admin/dashboard/school-names')"
        />
    </div>
</template>
