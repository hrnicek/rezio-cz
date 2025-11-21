<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    title?: string; // Added title prop as it's used in <Head>
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    title: '', // Default for title
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="title" />
        <Toaster richColors position="top-right" />
        <slot />
    </AppLayout>
</template>
