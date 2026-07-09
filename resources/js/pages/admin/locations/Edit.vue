<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/locations/Form.vue';
import { index as locationsIndex, update } from '@/routes/admin/locations';

const props = defineProps<{
    location: {
        id: number;
        country: string;
        province: string;
        school_name: string;
    };
    countries: string[];
    provinces: { name: string; country: string | null }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Locations', href: locationsIndex().url },
            { title: 'Edit', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.put(update(props.location.id).url);
};
</script>

<template>
    <Head :title="`Edit ${location.school_name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Edit location" :description="location.school_name" />
        <Form
            :location="location"
            :countries="countries"
            :provinces="provinces"
            submit-label="Save changes"
            @submit="onSubmit"
        />
    </div>
</template>
