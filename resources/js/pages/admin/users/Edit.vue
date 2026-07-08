<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/users/Form.vue';
import { index as usersIndex, update } from '@/routes/admin/users';

const props = defineProps<{
    user: { id: number; name: string; email: string; roles: string[] };
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: usersIndex().url },
            { title: 'Edit', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.put(update(props.user.id).url);
};
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Edit user" :description="user.email" />
        <Form
            :user="user"
            :roles="roles"
            submit-label="Save changes"
            @submit="onSubmit"
        />
    </div>
</template>
