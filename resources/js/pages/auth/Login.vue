<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
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
        title: 'Sign in',
        description: 'Sign in to your administrator workspace',
    },
});

const showPassword = ref(false);

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Sign in" />

    <div
        v-if="status"
        class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-400"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-2">
            <Label for="email">Email Address</Label>
            <div class="relative">
                <Mail
                    class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="admin@example.com"
                    class="pl-10"
                />
            </div>
            <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
            <div class="flex items-center justify-between">
                <Label for="password">Password</Label>
                <TextLink
                    v-if="canResetPassword"
                    :href="request()"
                    :tabindex="5"
                    class="text-xs font-medium"
                >
                    Forgot Password?
                </TextLink>
            </div>
            <div class="relative">
                <Lock
                    class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="pl-10 pr-10"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                    :tabindex="-1"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <EyeOff v-if="showPassword" class="size-4" />
                    <Eye v-else class="size-4" />
                </button>
            </div>
            <InputError :message="errors.password" />
        </div>

        <div class="flex items-center gap-3">
            <Checkbox id="remember" name="remember" :tabindex="3" />
            <Label for="remember" class="text-sm font-normal leading-none">
                Keep me signed in
            </Label>
        </div>

        <Button
            type="submit"
            class="w-full"
            :tabindex="4"
            :disabled="processing"
            data-test="login-button"
        >
            <Spinner v-if="processing" />
            <template v-else>
                Sign In
            </template>
        </Button>
    </Form>

    <div class="mt-6 text-center text-xs text-muted-foreground">
        Access is limited to authorized administrators.
        Your session is protected and encrypted.
    </div>
</template>
