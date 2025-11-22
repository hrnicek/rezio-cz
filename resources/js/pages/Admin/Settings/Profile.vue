<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { TransitionRoot } from '@headlessui/vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = (usePage().props.auth as any).user;

const form = useForm({
    name: user.name,
    email: user.email,
});

declare const route: any;

const updateProfileInformation = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success if needed
        },
    });
};
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Profile Information</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your account's profile information and email address.</p>
            </div>

            <form @submit.prevent="updateProfileInformation" class="space-y-6">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" type="text" required autocomplete="name" />
                    <div v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required autocomplete="username" />
                    <div v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</div>
                </div>

                <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-show="props.status === 'verification-link-sent'"
                        class="mt-2 font-medium text-sm text-green-600 dark:text-green-400"
                    >
                        A new verification link has been sent to your email address.
                    </div>
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
