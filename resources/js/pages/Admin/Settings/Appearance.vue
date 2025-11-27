<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { ref, onMounted, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

const theme = ref('system');

onMounted(() => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    if (localStorage.theme) {
        theme.value = localStorage.theme;
    } else {
        theme.value = 'system';
    }
});

watch(theme, (value) => {
    if (value === 'system') {
        localStorage.removeItem('theme');
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    } else if (value === 'dark') {
        localStorage.theme = 'dark';
        document.documentElement.classList.add('dark');
    } else {
        localStorage.theme = 'light';
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-foreground">Appearance</h3>
                <p class="mt-1 text-sm text-muted-foreground">Customize the look and feel of the application.</p>
            </div>

            <div class="space-y-4">
                <RadioGroup v-model="theme" class="grid grid-cols-3 gap-4">
                    <div>
                        <RadioGroupItem id="light" value="light" class="peer sr-only" />
                        <Label
                            for="light"
                            class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                        >
                            <div class="mb-3 h-20 w-full rounded-md bg-[#ecedef]" />
                            <span class="block w-full text-center font-normal">Light</span>
                        </Label>
                    </div>
                    <div>
                        <RadioGroupItem id="dark" value="dark" class="peer sr-only" />
                        <Label
                            for="dark"
                            class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                        >
                            <div class="mb-3 h-20 w-full rounded-md bg-[#18181b]" />
                            <span class="block w-full text-center font-normal">Dark</span>
                        </Label>
                    </div>
                    <div>
                        <RadioGroupItem id="system" value="system" class="peer sr-only" />
                        <Label
                            for="system"
                            class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                        >
                            <div class="mb-3 h-20 w-full rounded-md bg-[#ecedef] dark:bg-[#18181b]" />
                            <span class="block w-full text-center font-normal">System</span>
                        </Label>
                    </div>
                </RadioGroup>
            </div>
        </div>
    </SettingsLayout>
</template>
