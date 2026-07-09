<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/locations/Form.vue';
import { index as locationsIndex, store } from '@/routes/admin/locations';

defineProps<{
    countries: string[];
    provinces: { name: string; country: string | null }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Locations', href: locationsIndex().url },
            { title: 'Create', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Create location" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Create location" description="Add a new school location" />
        <Form
            :countries="countries"
            :provinces="provinces"
            submit-label="Create location"
            @submit="onSubmit"
        />
    </div>
</template>
