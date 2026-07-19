<script setup lang="ts">
import { ref } from 'vue'
import { useAppearance } from '@/composables/useAppearance'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { toast } from 'vue-sonner'

const { appearance, updateAppearance } = useAppearance()

// ─── Active tab ───────────────────────────────────────
const activeTab = ref<'profile' | 'password' | 'appearance' | 'language'>('profile')

// ─── Profile form ─────────────────────────────────────
const profileForm = ref({
    name: 'Admin User',
    email: 'admin@classengage.com',
    phone: '+855 12 345 678',
    bio: 'Platform administrator with 5+ years of experience in educational technology.',
})

const profileSaving = ref(false)
function saveProfile() {
    profileSaving.value = true
    setTimeout(() => {
        profileSaving.value = false
        toast.success('Profile updated successfully')
    }, 1000)
}

// ─── Password form ────────────────────────────────────
const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})
const passwordSaving = ref(false)
function savePassword() {
    if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
        toast.error('Passwords do not match')
        return
    }
    passwordSaving.value = true
    setTimeout(() => {
        passwordSaving.value = false
        toast.success('Password changed successfully')
        passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
    }, 1000)
}

// ─── Avatar upload ────────────────────────────────────
const avatarPreview = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

function triggerAvatarUpload() {
    fileInput.value?.click()
}

function handleAvatarUpload(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = (e) => {
        avatarPreview.value = e.target?.result as string
        toast.success('Avatar uploaded successfully')
    }
    reader.readAsDataURL(file)
}

// ─── Language ─────────────────────────────────────────
const languages = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'km', name: 'ភាសាខ្មែរ (Khmer)', flag: '🇰🇭' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'zh', name: '中文 (Chinese)', flag: '🇨🇳' },
]
const selectedLanguage = ref('en')

const tabs = [
    { id: 'profile' as const, label: 'Profile', icon: 'user' },
    { id: 'password' as const, label: 'Password', icon: 'lock' },
    { id: 'appearance' as const, label: 'Appearance', icon: 'palette' },
    { id: 'language' as const, label: 'Language', icon: 'globe' },
]
</script>

<template>
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Admin Settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your profile, preferences, and account settings</p>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mb-8 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl w-fit">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all cursor-pointer"
                :class="activeTab === tab.id
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'" class="space-y-6">
            <!-- Avatar Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Profile Photo</h2>
                <div class="flex items-center gap-5">
                    <div class="relative group">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white dark:ring-gray-800 shadow-md">
                            <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full rounded-full object-cover" />
                            <span v-else>AU</span>
                        </div>
                        <button
                            class="absolute -bottom-1 -right-1 w-7 h-7 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                            @click="triggerAvatarUpload"
                            title="Change photo"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </button>
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleAvatarUpload" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Upload a new photo</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">PNG, JPG or WEBP. Max 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Personal Information</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Update your profile details</p>

                <form @submit.prevent="saveProfile" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                            <Input v-model="profileForm.name" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                            <Input v-model="profileForm.email" type="email" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                            <Input v-model="profileForm.phone" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                        <textarea
                            v-model="profileForm.bio"
                            rows="3"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 resize-none"
                        ></textarea>
                    </div>
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="profileSaving" class="gap-2 cursor-pointer">
                            <div v-if="profileSaving" class="size-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            {{ profileSaving ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Tab -->
        <div v-if="activeTab === 'password'">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Change Password</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Ensure your account is using a strong password</p>

                <form @submit.prevent="savePassword" class="space-y-5 max-w-md">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                        <Input v-model="passwordForm.current_password" type="password" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                        <Input v-model="passwordForm.new_password" type="password" required />
                        <p class="text-xs text-gray-400 dark:text-gray-500">Must be at least 8 characters with mixed case and numbers</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                        <Input v-model="passwordForm.new_password_confirmation" type="password" required />
                    </div>
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="passwordSaving" class="gap-2 cursor-pointer">
                            <div v-if="passwordSaving" class="size-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            {{ passwordSaving ? 'Changing...' : 'Change Password' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Appearance Tab -->
        <div v-if="activeTab === 'appearance'">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Theme Settings</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Customize the appearance of the admin panel</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <button
                        class="flex flex-col items-center gap-3 p-5 rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                        :class="appearance === 'light'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                        @click="updateAppearance('light')"
                    >
                        <div class="w-16 h-12 rounded-lg bg-white border border-gray-200 shadow-sm flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Light</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Default light theme</p>
                        </div>
                        <div v-if="appearance === 'light'" class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </button>

                    <button
                        class="flex flex-col items-center gap-3 p-5 rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                        :class="appearance === 'dark'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                        @click="updateAppearance('dark')"
                    >
                        <div class="w-16 h-12 rounded-lg bg-gray-900 border border-gray-700 shadow-sm flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Dark</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Dark mode theme</p>
                        </div>
                        <div v-if="appearance === 'dark'" class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </button>

                    <button
                        class="flex flex-col items-center gap-3 p-5 rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                        :class="appearance === 'system'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                        @click="updateAppearance('system')"
                    >
                        <div class="w-16 h-12 rounded-lg bg-gradient-to-br from-white to-gray-900 border border-gray-300 shadow-sm flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">System</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Follow system theme</p>
                        </div>
                        <div v-if="appearance === 'system'" class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Language Tab -->
        <div v-if="activeTab === 'language'">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Language Preferences</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Choose your preferred interface language</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button
                        v-for="lang in languages"
                        :key="lang.code"
                        class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                        :class="selectedLanguage === lang.code
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                        @click="selectedLanguage = lang.code"
                    >
                        <span class="text-2xl">{{ lang.flag }}</span>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lang.name }}</p>
                        </div>
                        <div v-if="selectedLanguage === lang.code" class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
