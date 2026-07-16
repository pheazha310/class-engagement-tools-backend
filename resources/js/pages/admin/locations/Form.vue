<script setup lang="ts">
import { ref, computed } from 'vue'

type ProvinceObject = {
    name: string
    country: string | null
}

const props = defineProps<{
    countries: string[]
    provinces: ProvinceObject[]
    location?: {
        id: number
        country: string
        province: string
        school_name: string
    }
    submitLabel: string
}>()

const emit = defineEmits<{
    submit: [data: { school_name: string; country: string; province: string }]
    cancel: []
}>()

const form = ref({
    country: props.location?.country ?? '',
    province: props.location?.province ?? '',
    school_name: props.location?.school_name ?? '',
})

const filteredProvinces = computed(() =>
    props.provinces.filter((p) => p.country === form.value.country),
)

function submit() {
    emit('submit', {
        school_name: form.value.school_name,
        country: form.value.country,
        province: form.value.province,
    })
}
</script>

<template>
    <div class="max-w-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                <select
                    v-model="form.country"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                >
                    <option value="" disabled>Select a country</option>
                    <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
                </select>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Province</label>
                <select
                    v-model="form.province"
                    :disabled="!form.country"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 disabled:opacity-50"
                >
                    <option value="" disabled>{{ form.country ? 'Select a province' : 'Select a country first' }}</option>
                    <option v-for="province in filteredProvinces" :key="province.name" :value="province.name">{{ province.name }}</option>
                </select>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">School Name</label>
                <input
                    v-model="form.school_name"
                    required
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                />
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
