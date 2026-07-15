<script setup lang="ts">
const iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>'
</script>

<template>
    <div class="admin-placeholder">
        <div class="placeholder-icon" style="background: var(--color-info-light); color: var(--color-info);" v-html="iconHtml" />
        <h2>Reports</h2>
        <p>View detailed analytics and generate reports on platform usage, user engagement, and academic performance.</p>
    </div>
</template>
