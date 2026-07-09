<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Shield, Mail, Lock } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Enterprise Admin',
        description: 'Secure Institutional Gateway',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <div class="flex flex-col items-center">
        <!-- Shield Icon -->
        <div class="mb-8 flex h-20 w-20 items-center justify-center rounded-xl bg-blue-600 shadow-lg">
            <Shield class="h-12 w-12 text-white" />
        </div>

        <!-- Title -->
        <h1 class="mb-2 text-4xl font-bold text-gray-900 tracking-tight">Enterprise Admin</h1>
        <p class="mb-10 text-base text-gray-600 font-medium">Secure Institutional Gateway</p>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="w-full space-y-6"
        >
            <!-- Email Field -->
            <div class="space-y-6">
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-semibold text-gray-700">
                        Email Address
                    </Label>
                    <div class="relative">
                        <Mail class="absolute left-3 top-3.5 h-5 w-5 text-gray-500" />
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="name@company.com"
                            class="pl-10 h-12 bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-semibold text-gray-700">
                            Password
                        </Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700"
                            :tabindex="5"
                        >
                            Forgot Password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <Lock class="absolute left-3 top-3.5 h-5 w-5 text-gray-500" />
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="pl-10 h-12 bg-gray-50 border-2 border-gray-200 text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" class="border-2 border-gray-300" />
                    <Label for="remember" class="text-sm font-medium text-gray-700 cursor-pointer">
                        Keep me signed in
                    </Label>
                </div>

                <!-- Sign In Button -->
                <Button
                    type="submit"
                    class="w-full h-12 bg-blue-600 text-base font-semibold hover:bg-blue-700 shadow-md hover:shadow-lg transition-all duration-200"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    <span class="flex items-center justify-center gap-2">
                        Sign In
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </Button>
            </div>
        </Form>
    </div>
</template>

<style scoped>
/* Ensure the form is centered and has proper spacing */
:deep(.space-y-6 > * + *) {
    margin-top: 1.5rem;
}
</style>
