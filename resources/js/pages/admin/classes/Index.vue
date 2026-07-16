<script setup lang="ts">
const iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>'
</script>

<template>
    <div class="admin-placeholder">
        <div class="placeholder-icon bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400" v-html="iconHtml" />
        <h2>Class Management</h2>
        <p>Create and manage classes, assign teachers, and track student progress and engagement.</p>
    </div>
</template>
