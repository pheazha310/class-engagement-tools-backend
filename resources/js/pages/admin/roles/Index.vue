<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Lock, Pencil, Plus, Trash2 } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, destroy, edit, index as rolesIndex } from '@/routes/admin/roles';
import type { AdminRoleListItem } from '@/types';

defineProps<{ roles: AdminRoleListItem[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Roles', href: rolesIndex().url }],
    },
});

const deleteRole = (role: AdminRoleListItem) => {
    if (confirm(`Delete the "${role.name}" role?`)) {
        router.delete(destroy(role.id).url, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Roles" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between gap-4">
            <Heading title="Roles" description="Define roles and their permissions" />
            <Button as-child>
                <Link :href="create().url">
                    <Plus />
                    Add role
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="role in roles"
                :key="role.id"
                class="flex flex-col gap-3 rounded-xl border p-4"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold capitalize">{{ role.name }}</h3>
                        <Lock
                            v-if="role.is_protected"
                            class="size-3.5 text-muted-foreground"
                        />
                    </div>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="icon-sm" as-child>
                            <Link :href="edit(role.id).url">
                                <Pencil />
                                <span class="sr-only">Edit</span>
                            </Link>
                        </Button>
                        <Button
                            v-if="!role.is_protected"
                            variant="ghost"
                            size="icon-sm"
                            class="text-destructive hover:text-destructive"
                            @click="deleteRole(role)"
                        >
                            <Trash2 />
                            <span class="sr-only">Delete</span>
                        </Button>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground">
                    {{ role.users_count }} user{{ role.users_count === 1 ? '' : 's' }}
                </p>

                <div class="flex flex-wrap gap-1">
                    <Badge
                        v-for="permission in role.permissions"
                        :key="permission"
                        variant="secondary"
                    >
                        {{ permission }}
                    </Badge>
                    <span
                        v-if="role.permissions.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No permissions
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
