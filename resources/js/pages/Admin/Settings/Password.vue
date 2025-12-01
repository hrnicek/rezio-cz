<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';

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
        onSuccess: () => {
            form.reset();
            toast.success('Heslo bylo úspěšně změněno.');
        },
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
        <div class="space-y-6 max-w-2xl">
            <div>
                <h3 class="text-lg font-medium leading-6 text-foreground">Změna hesla</h3>
                <p class="mt-1 text-sm text-muted-foreground">Ujistěte se, že váš účet používá dlouhé a náhodné heslo, abyste zůstali v bezpečí.</p>
            </div>

            <form @submit.prevent="updatePassword" class="space-y-6">
                <div class="space-y-2">
                    <Label for="current_password">Současné heslo</Label>
                    <Input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        class="h-9"
                    />
                    <div v-if="form.errors.current_password" class="text-sm text-red-500">{{ form.errors.current_password }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="password">Nové heslo</Label>
                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        class="h-9"
                    />
                    <div v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Potvrzení nového hesla</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="h-9"
                    />
                    <div v-if="form.errors.password_confirmation" class="text-sm text-red-500">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="h-9">Uložit</Button>
                </div>
            </form>
        </div>
    </SettingsLayout>
</template>
