<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    roles: string[];
    user?: {
        id: number;
        name: string;
        email: string;
        roles: string[];
    };
    submitLabel: string;
}>();

const emit = defineEmits<{
    (e: 'submit', form: ReturnType<typeof useForm>): void;
}>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles ?? ([] as string[]),
});

const toggleRole = (role: string, checked: boolean) => {
    if (checked) {
        form.roles = [...form.roles, role];
    } else {
        form.roles = form.roles.filter((r) => r !== role);
    }
};

const submit = () => emit('submit', form);
</script>

<template>
    <form class="max-w-xl space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required autocomplete="name" />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" required autocomplete="email" />
            <InputError :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password">
                Password
                <span v-if="props.user" class="text-muted-foreground">
                    (leave blank to keep current)
                </span>
            </Label>
            <Input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
            />
            <InputError :message="form.errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation">Confirm password</Label>
            <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
            />
        </div>

        <div class="grid gap-3">
            <Label>Roles</Label>
            <div class="flex flex-col gap-2">
                <label
                    v-for="role in roles"
                    :key="role"
                    class="flex items-center gap-2 text-sm"
                >
                    <Checkbox
                        :model-value="form.roles.includes(role)"
                        @update:model-value="(checked) => toggleRole(role, checked === true)"
                    />
                    {{ role }}
                </label>
                <p v-if="roles.length === 0" class="text-sm text-muted-foreground">
                    No roles defined yet.
                </p>
            </div>
            <InputError :message="form.errors.roles" />
        </div>

        <div class="flex gap-2">
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
        </div>
    </form>
</template>
