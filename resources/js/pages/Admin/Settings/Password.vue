<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { TransitionRoot } from '@headlessui/vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

declare const route: any;

const updatePassword = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Update Password</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <form @submit.prevent="updatePassword" class="space-y-6">
                <div class="space-y-2">
                    <Label for="current_password">Current Password</Label>
                    <Input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.current_password" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.current_password }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="password">New Password</Label>
                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.password }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Save</Button>

                    <TransitionRoot
                        :show="form.recentlySuccessful"
                        enter="transition ease-in-out"
                        enter-from="opacity-0"
                        leave="transition ease-in-out"
                        leave-to="opacity-0"
                    >
                        <p class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                    </TransitionRoot>
                </div>
            </form>
        </div>
    </SettingsLayout>
</template>
