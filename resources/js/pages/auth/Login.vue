<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Shield, Mail, Lock, Eye, EyeOff, CheckCircle2, Building2 } from '@lucide/vue';
import { ref, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const showPassword = ref(false);
const isLoaded = ref(false);

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

onMounted(() => {
    requestAnimationFrame(() => {
        isLoaded.value = true;
    });
});
</script>

<template>
    <Head title="Administrator sign in" />

    <div class="admin-login-page">
        <div class="admin-login-bg-glows" aria-hidden="true">
            <div class="admin-login-bg-glow admin-login-bg-glow--1" />
            <div class="admin-login-bg-glow admin-login-bg-glow--2" />
            <div class="admin-login-bg-glow admin-login-bg-glow--3" />
        </div>

        <div class="admin-login-main">
            <!-- LEFT PANEL -->
            <div class="admin-login-left">
                <div class="admin-login-left-pattern" />
                <div class="admin-login-left-glows">
                    <div class="admin-login-left-glow admin-login-left-glow--1" />
                    <div class="admin-login-left-glow admin-login-left-glow--2" />
                    <div class="admin-login-left-glow admin-login-left-glow--3" />
                </div>
                <div class="admin-login-particles" aria-hidden="true">
                    <div class="admin-login-particle admin-login-particle--1" />
                    <div class="admin-login-particle admin-login-particle--2" />
                    <div class="admin-login-particle admin-login-particle--3" />
                    <div class="admin-login-particle admin-login-particle--4" />
                    <div class="admin-login-particle admin-login-particle--5" />
                    <div class="admin-login-particle admin-login-particle--6" />
                </div>

                <div class="admin-login-left-content">
                    <div class="admin-login-fade admin-login-fade--delay-75" :class="{ 'admin-login-fade--visible': isLoaded }">
                        <div class="admin-login-branding">
                            <div class="admin-login-branding-icon">
                                <Building2 />
                            </div>
                            <span class="admin-login-branding-text">Class Engage</span>
                        </div>
                    </div>

                    <div class="admin-login-hero">
                        <div class="admin-login-shield-wrapper admin-login-scale" :class="{ 'admin-login-scale--visible': isLoaded }">
                            <div class="admin-login-shield-glow" />
                            <div class="admin-login-shield-box">
                                <Shield />
                            </div>
                        </div>

                        <h1 class="admin-login-hero-title admin-login-fade admin-login-fade--delay-200" :class="{ 'admin-login-fade--visible': isLoaded }">
                            Class Engage Admin
                        </h1>

                        <p class="admin-login-hero-subtitle admin-login-fade admin-login-fade--delay-300" :class="{ 'admin-login-fade--visible': isLoaded }">
                            Manage your school community with confidence.
                        </p>

                        <p class="admin-login-hero-desc admin-login-fade admin-login-fade--delay-400" :class="{ 'admin-login-fade--visible': isLoaded }">
                            Secure access for the people who keep classes connected, active, and running smoothly.
                        </p>
                    </div>

                    <div class="admin-login-left-footer admin-login-fade admin-login-fade--delay-500" :class="{ 'admin-login-fade--visible': isLoaded }">
                        <Shield />
                            <span>Secure administration for your school</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="admin-login-right">
                <div class="admin-login-card-wrapper admin-login-slide-up" :class="{ 'admin-login-slide-up--visible': isLoaded }">
                    <div class="admin-login-card">
                        <div v-if="status" class="admin-login-status">
                            <CheckCircle2 />
                            {{ status }}
                        </div>

                        <div class="admin-login-card-icon">
                            <div class="admin-login-card-icon-box">
                                <Shield />
                            </div>
                        </div>

                        <div class="admin-login-card-header">
                            <h1 class="admin-login-card-title">Welcome back</h1>
                            <p class="admin-login-card-subtitle">Sign in to your administrator workspace</p>
                        </div>

                        <Form v-bind="store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }" class="admin-login-form">
                            <div class="admin-login-field">
                                <Label for="email" class="admin-login-label">Email Address</Label>
                                <div class="admin-login-input-group">
                                    <Mail class="admin-login-input-icon" />
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        placeholder="admin@example.com"
                                        aria-label="Email Address"
                                        class="admin-login-input admin-login-input--email"
                                    />
                                </div>
                                <InputError :message="errors.email" class="admin-login-input-error" />
                            </div>

                            <div class="admin-login-field">
                                <div class="admin-login-password-header">
                                    <Label for="password" class="admin-login-label">Password</Label>
                                    <TextLink
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="admin-login-forgot"
                                        :tabindex="5"
                                    >
                                        Forgot Password?
                                    </TextLink>
                                </div>
                                <div class="admin-login-input-group">
                                    <Lock class="admin-login-input-icon" />
                                    <Input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        name="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        placeholder="Enter your password"
                                        aria-label="Password"
                                        class="admin-login-input admin-login-input--password"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="admin-login-toggle-pw"
                                        :tabindex="-1"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    >
                                        <EyeOff v-if="showPassword" />
                                        <Eye v-else />
                                    </button>
                                </div>
                                <InputError :message="errors.password" class="admin-login-input-error" />
                            </div>

                            <div class="admin-login-remember">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    :tabindex="3"
                                    class="admin-login-checkbox"
                                />
                                <Label for="remember" class="admin-login-remember-label">
                                    Keep me signed in
                                </Label>
                            </div>

                            <div class="admin-login-submit-wrapper">
                                <Button
                                    type="submit"
                                    class="admin-login-submit"
                                    :tabindex="4"
                                    :disabled="processing"
                                    data-test="login-button"
                                    aria-label="Sign in to your account"
                                >
                                    <Spinner v-if="processing" class="admin-login-submit-spinner" />
                                    <span v-else class="admin-login-submit-content">
                                        Sign In
                                        <svg class="admin-login-submit-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </Button>
                                <div class="admin-login-submit-glow" />
                            </div>
                        </Form>
                    </div>

                    <div class="admin-login-card-footer">
                        <div class="admin-login-divider">
                            <div class="admin-login-divider-line admin-login-divider-line--left" />
                            <Shield class="admin-login-divider-icon" />
                            <div class="admin-login-divider-line admin-login-divider-line--right" />
                        </div>
                        <p class="admin-login-footer-text">
                            Access is limited to authorized administrators<br class="admin-login-br" />
                            Your session is protected and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
