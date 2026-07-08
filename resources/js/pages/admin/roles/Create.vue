<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/roles/Form.vue';
import { index as rolesIndex, store } from '@/routes/admin/roles';

defineProps<{ permissions: string[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Roles', href: rolesIndex().url },
            { title: 'Create', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Create role" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Create role" description="Define a role and its permissions" />
        <Form :permissions="permissions" submit-label="Create role" @submit="onSubmit" />
    </div>
</template>
