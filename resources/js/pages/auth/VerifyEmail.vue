<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Email verification',
        description:
            'Please verify your email address by clicking on the link we just emailed to you.',
    },
});

defineProps<{
    status?: string;
}>();

function handleLogout() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Email verification" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        A new verification link has been sent to the email address you provided
        during registration.
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            Resend verification email
        </Button>

        <Button variant="link" as-child class="mx-auto block text-sm" @click="handleLogout">
            Log out
        </Button>
    </Form>
</template>
