<script setup lang="ts">
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'

// ─── Login History ────────────────────────────────────
const loginHistory = ref([
    { id: 1, device: 'Chrome on Windows', ip: '192.168.1.100', location: 'Phnom Penh, KH', time: '2 minutes ago', status: 'current' },
    { id: 2, device: 'Safari on iPhone', ip: '192.168.1.101', location: 'Phnom Penh, KH', time: '3 hours ago', status: 'active' },
    { id: 3, device: 'Firefox on macOS', ip: '203.0.113.45', location: 'Siem Reap, KH', time: '2 days ago', status: 'active' },
    { id: 4, device: 'Chrome on Android', ip: '198.51.100.22', location: 'Battambang, KH', time: '1 week ago', status: 'expired' },
    { id: 5, device: 'Edge on Windows', ip: '192.0.2.88', location: 'Unknown', time: '2 weeks ago', status: 'expired' },
])

// ─── Active Sessions ──────────────────────────────────
const activeSessions = ref([
    { id: 1, device: 'Chrome 128.0 - Windows 11', ip: '192.168.1.100', lastActive: 'Active now', browser: 'Chrome', os: 'Windows' },
    { id: 2, device: 'Safari 18.0 - iPhone iOS 18', ip: '192.168.1.101', lastActive: '3 hours ago', browser: 'Safari', os: 'iOS' },
    { id: 3, device: 'Firefox 130.0 - macOS Sonoma', ip: '203.0.113.45', lastActive: '2 days ago', browser: 'Firefox', os: 'macOS' },
])

// ─── Activity Logs ────────────────────────────────────
const activityLogs = ref([
    { id: 1, action: 'Profile updated', details: 'Changed display name', timestamp: '1 hour ago', ip: '192.168.1.100' },
    { id: 2, action: 'Password changed', details: 'Password updated successfully', timestamp: '3 days ago', ip: '192.168.1.100' },
    { id: 3, action: 'Login from new device', details: 'Chrome on Windows 11', timestamp: '5 days ago', ip: '192.168.1.100' },
    { id: 4, action: 'Two-factor enabled', details: 'TOTP authentication activated', timestamp: '2 weeks ago', ip: '192.168.1.100' },
    { id: 5, action: 'Login from new location', details: 'Siem Reap, Cambodia', timestamp: '3 weeks ago', ip: '203.0.113.45' },
])

function terminateSession(sessionId: number) {
    const session = activeSessions.value.find(s => s.id === sessionId)
    if (session && confirm(`Terminate this session from ${session.device}?`)) {
        activeSessions.value = activeSessions.value.filter(s => s.id !== sessionId)
    }
}

function terminateAllOtherSessions() {
    if (confirm('Terminate all other sessions? You will remain logged in on this device.')) {
        // Keep only the current active session (first one)
        const currentSession = activeSessions.value.find(s => s.lastActive === 'Active now')
        activeSessions.value = currentSession ? [currentSession] : []
    }
}
</script>

<template>
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Security</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor your account security and manage active sessions</p>
        </div>

        <div class="space-y-6">
            <!-- Active Sessions -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Active Sessions</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ activeSessions.length }} active session{{ activeSessions.length !== 1 ? 's' : '' }}</p>
                    </div>
                    <Button
                        v-if="activeSessions.length > 1"
                        variant="destructive"
                        size="sm"
                        class="cursor-pointer text-xs"
                        @click="terminateAllOtherSessions"
                    >
                        Sign out all others
                    </Button>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div
                        v-for="session in activeSessions"
                        :key="session.id"
                        class="flex items-center gap-4 px-5 py-4"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 shrink-0">
                            <svg v-if="session.browser === 'Chrome'" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="21.17" y1="8" x2="12" y2="8"/><line x1="3.95" y1="6.06" x2="8.54" y2="14"/><line x1="10.88" y1="21.94" x2="15.46" y2="14"/></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ session.device }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">IP: {{ session.ip }} · Last active: {{ session.lastActive }}</p>
                        </div>
                        <span v-if="session.lastActive === 'Active now'" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-emerald-50 text-emerald-700 rounded-full dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                            Current
                        </span>
                        <Button
                            v-else
                            variant="ghost"
                            size="sm"
                            class="text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer text-xs"
                            @click="terminateSession(session.id)"
                        >
                            Terminate
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Login History -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Login History</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Recent login attempts and active sessions</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden sm:table-cell">IP Address</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden md:table-cell">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Time</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            <tr v-for="login in loginHistory" :key="login.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ login.device }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell font-mono">{{ login.ip }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ login.location }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400">{{ login.time }}</td>
                                <td class="px-5 py-3.5">
                                    <Badge
                                        :variant="login.status === 'current' ? 'default' : login.status === 'active' ? 'secondary' : 'outline'"
                                        class="text-xs capitalize"
                                    >
                                        {{ login.status }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Account Activity</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Security-related account activity</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div
                        v-for="log in activityLogs"
                        :key="log.id"
                        class="flex items-start gap-3 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 shrink-0 mt-0.5">
                            <svg v-if="log.action.includes('Login')" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            <svg v-else-if="log.action.includes('Password')" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <svg v-else-if="log.action.includes('two-factor') || log.action.includes('2FA')" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <strong class="font-semibold text-gray-900 dark:text-white">{{ log.action }}</strong>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ log.details }} · {{ log.timestamp }} · IP: {{ log.ip }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Denied Page Preview -->
        <div class="mt-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Access Denied</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">You don&rsquo;t have permission to access this resource. Contact your administrator if you believe this is a mistake.</p>
                <div class="flex items-center justify-center gap-2">
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        403 Forbidden
                    </span>
                    <router-link to="/admin/dashboard">
                        <Button variant="outline" size="sm" class="cursor-pointer">
                            Go to Dashboard
                        </Button>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
