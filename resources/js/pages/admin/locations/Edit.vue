<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { get, put } from '@/services/api'
import Form from '@/pages/admin/locations/Form.vue'

const route = useRoute()
const router = useRouter()

const location = ref<{ id: number; school_name: string; country: string; province: string } | null>(null)
const lookupData = ref<{ countries: string[]; provinces: { name: string; country: string | null }[] }>({ countries: [], provinces: [] })
const loading = ref(true)

onMounted(async () => {
    const [locRes, lookupRes] = await Promise.all([
        get<any>(`/api/admin/locations/${route.params.id}`),
        get<{ countries: string[]; provinces: { name: string; country: string | null }[] }>('/api/admin/locations/lookup/data'),
    ])
    if (locRes.data) location.value = locRes.data
    if (lookupRes.data) lookupData.value = lookupRes.data
    loading.value = false
})

async function onSubmit(data: { school_name: string; country: string; province: string }) {
    const res = await put(`/api/admin/locations/${route.params.id}`, data)
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit School Name</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" v-if="location">{{ location.school_name }}</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2 py-4 text-sm text-gray-400">
            <div class="w-4 h-4 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
            Loading data...
        </div>

        <Form
            v-else-if="location"
            :countries="lookupData.countries"
            :provinces="lookupData.provinces"
            :location="location"
            submit-label="Save Changes"
            @submit="onSubmit"
            @cancel="router.push('/admin/dashboard/school-names')"
        />

        <div v-else class="text-center py-10 text-gray-400">School name not found.</div>
    </div>
</template>
