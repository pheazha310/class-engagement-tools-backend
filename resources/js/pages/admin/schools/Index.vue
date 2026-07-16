<script setup lang="ts">
const iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>'
</script>

<template>
    <div class="admin-placeholder">
        <div class="placeholder-icon bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400" v-html="iconHtml" />
        <h2>School Management</h2>
        <p>Add, edit, and manage schools and educational institutions registered on the platform.</p>
    </div>
</template>
