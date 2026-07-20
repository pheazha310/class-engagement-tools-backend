<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

defineProps<Props>();

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

async function handleLogout() {
    try {
        await fetch('/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    } catch {
        // fallback
    }
    window.location.href = '/login';
}
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <button
            class="group flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm"
            @click="handleLogout"
            data-test="logout-button"
        >
            <LogOut class="h-4 w-4" />
            Log out
        </button>
    </DropdownMenuItem>
</template>
