<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
<<<<<<< HEAD
import { Mail, Lock, Eye, EyeOff, ArrowRight, Shield } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Welcome back',
        description: 'Sign in to your account to continue',
    },
});

const showPassword = ref(false);
const emailFocused = ref(false);
const passwordFocused = ref(false);

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Sign in" />

    <!-- Status message -->
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div
            v-if="status"
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm font-medium text-emerald-700 backdrop-blur-sm dark:border-emerald-800/50 dark:bg-emerald-950/40 dark:text-emerald-400"
        >
            <div class="flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ status }}
            </div>
        </div>
    </Transition>

    <!-- Login Form -->
    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <!-- Email Field -->
        <div class="grid gap-1.5">
            <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Email Address
            </Label>
            <div class="relative">
                <div
                    class="absolute left-3 top-1/2 z-10 -translate-y-1/2 transition-colors duration-200"
                    :class="emailFocused ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'"
                >
                    <Mail class="size-4" />
                </div>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="admin@example.com"
                    class="h-11 pl-10 border-gray-200 bg-white/50 text-sm shadow-sm transition-all duration-200 placeholder:text-gray-400 focus-visible:border-blue-400 focus-visible:ring-4 focus-visible:ring-blue-100 dark:border-gray-700 dark:bg-gray-800/50 dark:placeholder:text-gray-500 dark:focus-visible:border-blue-500 dark:focus-visible:ring-blue-900/30"
                    :class="{ 'ring-4 ring-blue-100 dark:ring-blue-900/30 border-blue-400 dark:border-blue-500': emailFocused }"
                    @focus="emailFocused = true"
                    @blur="emailFocused = false"
                />
            </div>
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <InputError v-if="errors.email" :message="errors.email" />
            </Transition>
        </div>

        <!-- Password Field -->
        <div class="grid gap-1.5">
            <div class="flex items-center justify-between">
                <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password
                </Label>
                <TextLink
                    v-if="canResetPassword"
                    :href="request()"
                    :tabindex="5"
                    class="text-xs font-medium text-blue-600 transition-all duration-200 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Forgot password?
                </TextLink>
            </div>
            <div class="relative">
                <div
                    class="absolute left-3 top-1/2 z-10 -translate-y-1/2 transition-colors duration-200"
                    :class="passwordFocused ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400'"
                >
                    <Lock class="size-4" />
                </div>
                <Input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="h-11 pl-10 pr-12 border-gray-200 bg-white/50 text-sm shadow-sm transition-all duration-200 placeholder:text-gray-400 focus-visible:border-blue-400 focus-visible:ring-4 focus-visible:ring-blue-100 dark:border-gray-700 dark:bg-gray-800/50 dark:placeholder:text-gray-500 dark:focus-visible:border-blue-500 dark:focus-visible:ring-blue-900/30"
                    :class="{ 'ring-4 ring-blue-100 dark:ring-blue-900/30 border-blue-400 dark:border-blue-500': passwordFocused }"
                    @focus="passwordFocused = true"
                    @blur="passwordFocused = false"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 z-10 -translate-y-1/2 text-gray-400 transition-all duration-200 hover:text-gray-600 dark:hover:text-gray-300"
                    :tabindex="-1"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <Transition
                        mode="out-in"
                        enter-active-class="transition-all duration-200"
                        leave-active-class="transition-all duration-200 absolute"
                        enter-from-class="opacity-0 rotate-12 scale-75"
                        enter-to-class="opacity-100 rotate-0 scale-100"
                        leave-from-class="opacity-100 rotate-0 scale-100"
                        leave-to-class="opacity-0 -rotate-12 scale-75"
                    >
                        <EyeOff v-if="showPassword" class="size-4" key="eye-off" />
                        <Eye v-else class="size-4" key="eye" />
                    </Transition>
                </button>
            </div>
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <InputError v-if="errors.password" :message="errors.password" />
            </Transition>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-2.5">
            <Checkbox
                id="remember"
                name="remember"
                :tabindex="3"
                class="data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600 dark:data-[state=checked]:bg-blue-500"
            />
            <Label for="remember" class="text-sm font-normal text-gray-600 leading-none dark:text-gray-400">
                Keep me signed in
            </Label>
        </div>

        <!-- Submit Button -->
        <Button
            type="submit"
            class="group relative h-11 w-full overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-sm font-semibold text-white shadow-lg shadow-blue-200/50 transition-all duration-300 hover:from-blue-700 hover:to-indigo-700 hover:shadow-blue-300/50 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-70 dark:shadow-indigo-900/30 dark:hover:shadow-indigo-800/30"
            :tabindex="4"
            :disabled="processing"
            data-test="login-button"
        >
            <span class="relative z-10 flex items-center justify-center gap-2">
                <Spinner v-if="processing" class="size-4 border-white" />
                <template v-else>
                    Sign In
                    <ArrowRight class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                </template>
            </span>
        </Button>
    </Form>

    <!-- Divider -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <Separator class="w-full" />
        </div>
        <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-card px-3 text-gray-400 dark:text-gray-500">Access</span>
        </div>
    </div>

    <!-- Footer note -->
    <p class="text-center text-xs leading-relaxed text-gray-400 dark:text-gray-500">
        Authorized administrators only.
        <br class="sm:hidden" />
        Your session is protected with end-to-end encryption.
    </p>
</template>
