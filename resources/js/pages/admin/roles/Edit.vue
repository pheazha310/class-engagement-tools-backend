<script setup lang="ts">
import type { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Form from '@/pages/admin/roles/Form.vue';
import { index as rolesIndex, update } from '@/routes/admin/roles';

const props = defineProps<{
    role: { id: number; name: string; permissions: string[]; is_protected: boolean };
    permissions: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Roles', href: rolesIndex().url },
            { title: 'Edit', href: '' },
        ],
    },
});

const onSubmit = (form: ReturnType<typeof useForm>) => {
    form.put(update(props.role.id).url);
};
</script>

<template>
    <Head :title="`Edit ${role.name} role`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Edit role" :description="`Adjust permissions for ${role.name}`" />
        <Form
            :role="role"
            :permissions="permissions"
            submit-label="Save changes"
            @submit="onSubmit"
        />
    </div>
</template>
