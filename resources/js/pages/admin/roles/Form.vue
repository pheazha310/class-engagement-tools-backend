<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    permissions: string[];
    role?: {
        id: number;
        name: string;
        permissions: string[];
        is_protected: boolean;
    };
    submitLabel: string;
}>();

const emit = defineEmits<{
    (e: 'submit', form: ReturnType<typeof useForm>): void;
}>();

const form = useForm({
    name: props.role?.name ?? '',
    permissions: props.role?.permissions ?? ([] as string[]),
});

const togglePermission = (permission: string, checked: boolean) => {
    if (checked) {
        form.permissions = [...form.permissions, permission];
    } else {
        form.permissions = form.permissions.filter((p) => p !== permission);
    }
};

const submit = () => emit('submit', form);
</script>

<template>
    <form class="max-w-xl space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Role name</Label>
            <Input
                id="name"
                v-model="form.name"
                required
                :disabled="props.role?.is_protected"
            />
            <p v-if="props.role?.is_protected" class="text-xs text-muted-foreground">
                This is a built-in role; its name cannot be changed.
            </p>
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-3">
            <Label>Permissions</Label>
            <div class="grid gap-2 sm:grid-cols-2">
                <label
                    v-for="permission in permissions"
                    :key="permission"
                    class="flex items-center gap-2 text-sm"
                >
                    <Checkbox
                        :model-value="form.permissions.includes(permission)"
                        @update:model-value="(checked) => togglePermission(permission, checked === true)"
                    />
                    {{ permission }}
                </label>
                <p v-if="permissions.length === 0" class="text-sm text-muted-foreground">
                    No permissions defined yet.
                </p>
            </div>
            <InputError :message="form.errors.permissions" />
        </div>

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
        </div>
    </form>
</template>
