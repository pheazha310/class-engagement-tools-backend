<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as locationsIndex, create, destroy, edit } from '@/routes/admin/locations';
import type { AdminLocationListItem, Paginated } from '@/types';

const props = defineProps<{
    locations: Paginated<AdminLocationListItem>;
    filters: { search: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Locations', href: locationsIndex().url }],
    },
});

const search = ref(props.filters.search);
let debounce: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(
            locationsIndex().url,
            { search: value },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }, 300);
});

const deleteLocation = (location: AdminLocationListItem) => {
    if (confirm(`Delete ${location.school_name}? This cannot be undone.`)) {
        router.delete(destroy(location.id).url, { preserveScroll: true });
    }
};

const paginationLabel = (label: string) =>
    label.replace('&laquo;', '‹').replace('&raquo;', '›').replace(/&[a-z]+;/g, '').trim();
</script>

<template>
    <Head title="Locations" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between gap-4">
            <Heading title="Locations" description="Manage school locations" />
            <Button as-child>
                <Link :href="create().url">
                    <Plus />
                    Add location
                </Link>
            </Button>
        </div>

        <Input
            v-model="search"
            type="search"
            placeholder="Search by school name, province, or country…"
            class="max-w-sm"
        />

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">School Name</th>
                        <th class="px-4 py-3 font-medium">Country</th>
                        <th class="px-4 py-3 font-medium">Province</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="location in locations.data"
                        :key="location.id"
                        class="border-b last:border-0 hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">{{ location.school_name }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ location.country }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ location.province }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="icon-sm" as-child>
                                    <Link :href="edit(location.id).url">
                                        <Pencil />
                                        <span class="sr-only">Edit</span>
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="deleteLocation(location)"
                                >
                                    <Trash2 />
                                    <span class="sr-only">Delete</span>
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="locations.data.length === 0">
                        <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">
                            No locations found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="locations.last_page > 1"
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <span>Showing {{ locations.from }}–{{ locations.to }} of {{ locations.total }}</span>
            <div class="flex gap-1">
                <Button
                    v-for="link in locations.links"
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
