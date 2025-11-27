<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

declare const route: any;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center gap-2">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1 text-center">
                        <h1 class="text-xl font-medium tracking-tight">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-col gap-6 rounded-lg border bg-card text-card-foreground p-6">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
