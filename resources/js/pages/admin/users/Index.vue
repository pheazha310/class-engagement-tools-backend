<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as usersIndex } from '@/routes/admin/users';
import { create, destroy, edit } from '@/routes/admin/users';
import type { AdminUserListItem, Paginated } from '@/types';

const props = defineProps<{
    users: Paginated<AdminUserListItem>;
    filters: { search: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: usersIndex().url }],
    },
});

const search = ref(props.filters.search);
let debounce: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(
            usersIndex().url,
            { search: value },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }, 300);
});

const deleteUser = (user: AdminUserListItem) => {
    if (confirm(`Delete ${user.name}? This cannot be undone.`)) {
        router.delete(destroy(user.id).url, { preserveScroll: true });
    }
};

const paginationLabel = (label: string) =>
    label.replace('&laquo;', '‹').replace('&raquo;', '›').replace(/&[a-z]+;/g, '').trim();
</script>

<template>
    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between gap-4">
            <Heading title="Users" description="Manage accounts and their roles" />
            <Button as-child>
                <Link :href="create().url">
                    <Plus />
                    Add user
                </Link>
            </Button>
        </div>

        <Input
            v-model="search"
            type="search"
            placeholder="Search by name or email…"
            class="max-w-sm"
        />

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Roles</th>
                        <th class="px-4 py-3 font-medium">Verified</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="border-b last:border-0 hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role"
                                    variant="secondary"
                                >
                                    {{ role }}
                                </Badge>
                                <span
                                    v-if="user.roles.length === 0"
                                    class="text-muted-foreground"
                                >
                                    —
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="user.email_verified_at ? 'default' : 'outline'"
                            >
                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="icon-sm" as-child>
                                    <Link :href="edit(user.id).url">
                                        <Pencil />
                                        <span class="sr-only">Edit</span>
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="deleteUser(user)"
                                >
                                    <Trash2 />
                                    <span class="sr-only">Delete</span>
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                            No users found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="users.last_page > 1"
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <span>Showing {{ users.from }}–{{ users.to }} of {{ users.total }}</span>
            <div class="flex gap-1">
                <Button
                    v-for="link in users.links"
                    :key="link.label"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    :class="{ 'bg-accent': link.active }"
                    @click="link.url && router.get(link.url, {}, { preserveScroll: true })"
                >
                    {{ paginationLabel(link.label) }}
                </Button>
            </div>
        </div>
    </div>
</template>
