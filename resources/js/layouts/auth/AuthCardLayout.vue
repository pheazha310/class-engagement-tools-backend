<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-4 md:p-10 dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950">
        <!-- Animated background orbs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-[500px] w-[500px] animate-pulse rounded-full bg-blue-400/10 blur-3xl dark:bg-blue-600/10" style="animation-duration: 8s;" />
            <div class="absolute -bottom-40 -right-40 h-[500px] w-[500px] animate-pulse rounded-full bg-indigo-400/10 blur-3xl dark:bg-indigo-600/10" style="animation-duration: 10s; animation-delay: 1s;" />
            <div class="absolute left-1/3 top-1/2 h-[300px] w-[300px] animate-pulse rounded-full bg-violet-400/10 blur-3xl dark:bg-violet-600/10" style="animation-duration: 12s; animation-delay: 2s;" />
        </div>

        <!-- Subtle grid pattern overlay -->
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(99,102,241,0.03)_1px,transparent_1px),linear-gradient(to_right,rgba(99,102,241,0.03)_1px,transparent_1px)] bg-[size:64px_64px] dark:bg-[linear-gradient(rgba(99,102,241,0.05)_1px,transparent_1px),linear-gradient(to_right,rgba(99,102,241,0.05)_1px,transparent_1px)]" />

        <div class="relative flex w-full max-w-md flex-col gap-6">
            <!-- Logo -->
            <Link
                :href="dashboard()"
                class="group flex items-center gap-2.5 self-center font-medium transition-all duration-300 hover:opacity-80"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg shadow-blue-200/50 transition-all duration-300 group-hover:shadow-blue-300/50 group-hover:scale-105 dark:shadow-indigo-900/50">
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </div>
                <span class="text-lg font-bold tracking-tight text-gray-800 dark:text-white">ClassEngage</span>
            </Link>

            <!-- Card -->
            <div class="flex flex-col gap-6">
                <Card class="rounded-2xl border border-gray-200/60 bg-white/90 shadow-xl shadow-gray-200/50 backdrop-blur-sm transition-all duration-300 dark:border-gray-800/60 dark:bg-gray-900/90 dark:shadow-gray-950/50">
                    <CardHeader v-if="title" class="px-8 pb-0 pt-8 text-center">
                        <CardTitle class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ title }}
                        </CardTitle>
                        <CardDescription class="mt-1.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-8 py-8">
                        <slot />
                    </CardContent>
                </Card>

                <!-- Footer text -->
                <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                    Secure &middot; Encrypted &middot; Protected
                </p>
            </div>
        </div>
    </div>
</template>
