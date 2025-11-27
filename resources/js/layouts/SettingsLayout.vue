<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const navigation = [
    { name: 'Profile', route: 'profile.edit', icon: 'UserIcon' },
    { name: 'Password', route: 'user-password.edit', icon: 'LockClosedIcon' },
    { name: 'Two Factor Auth', route: 'two-factor.show', icon: 'ShieldCheckIcon' },
    { name: 'Appearance', route: 'appearance.edit', icon: 'MoonIcon' },
];

declare const route: any;
</script>

<template>
    <AppLayout title="Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Sidebar -->
                    <aside class="w-full md:w-64 shrink-0">
                        <nav class="flex flex-col space-y-1">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="route(item.route)"
                                :class="[
                                    route().current(item.route)
                                        ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white',
                                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md'
                                ]"
                            >
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </nav>
                    </aside>

                    <!-- Content -->
                    <div class="flex-1 bg-white dark:bg-gray-800 border border-border sm:rounded-md p-6">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
