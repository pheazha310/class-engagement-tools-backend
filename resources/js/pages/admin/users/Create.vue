<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/users/Form.vue';
import { index as usersIndex, store } from '@/routes/admin/users';

defineProps<{ roles: string[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: usersIndex().url },
            { title: 'Create', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Create user" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Create user" description="Add a new account" />
        <Form :roles="roles" submit-label="Create user" @submit="onSubmit" />
    </div>
</template>
