<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-muted/30 dark:bg-muted/10">
        <Head title="Online Check-in" />
        
        <main class="py-10">
            <div class="mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <slot />
            </div>
        </main>
    </div>
</template>
