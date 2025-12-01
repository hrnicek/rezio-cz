<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';

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
            toast.success('Profil byl úspěšně aktualizován.');
        },
    });
};
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6 max-w-2xl">
            <div>
                <h3 class="text-lg font-medium leading-6 text-foreground">Informace o profilu</h3>
                <p class="mt-1 text-sm text-muted-foreground">Aktualizujte své profilové údaje a e-mailovou adresu.</p>
            </div>

            <form @submit.prevent="updateProfileInformation" class="space-y-6">
                <div class="space-y-2">
                    <Label for="name">Jméno</Label>
                    <Input id="name" v-model="form.name" type="text" required autocomplete="name" class="h-9" />
                    <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required autocomplete="username" class="h-9" />
                    <div v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</div>
                </div>

                <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-yellow-600">
                        Vaše e-mailová adresa není ověřena.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Klikněte zde pro opětovné odeslání ověřovacího e-mailu.
                        </Link>
                    </p>

                    <div
                        v-show="props.status === 'verification-link-sent'"
                        class="mt-2 font-medium text-sm text-green-600"
                    >
                        Nový ověřovací odkaz byl odeslán na vaši e-mailovou adresu.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="h-9">Uložit</Button>
                </div>
            </form>
        </div>
    </SettingsLayout>
</template>
