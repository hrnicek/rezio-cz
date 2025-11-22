<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';

const props = defineProps<{
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
}>();

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref<string | null>(null);
const setupKey = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);

const confirmationCode = ref('');

const twoFactorEnabled = computed(() => !enabling.value && props.twoFactorEnabled);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationCode.value = '';
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    router.post(route('two-factor.confirm'), {
        code: confirmationCode.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes')).then(() => {
        showRecoveryCodes();
    });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};

declare const route: any;
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Two Factor Authentication</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add additional security to your account using two factor authentication.</p>
            </div>

            <div v-if="twoFactorEnabled && !confirming">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    You have enabled two factor authentication.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
            </div>

            <div v-else-if="twoFactorEnabled && confirming">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Finish enabling two factor authentication.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
                </p>
            </div>

            <div v-else>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    You have not enabled two factor authentication.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 p-2 inline-block bg-white">
                        <div v-html="qrCode" />
                    </div>

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            Setup Key: <span v-html="setupKey"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <Label for="code">Code</Label>
                        <Input
                            id="code"
                            v-model="confirmationCode"
                            type="text"
                            name="code"
                            class="block mt-1 w-1/2"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication"
                        />
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && !confirming">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 rounded-lg">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="!twoFactorEnabled">
                    <Button :disabled="enabling" @click="enableTwoFactorAuthentication">
                        Enable
                    </Button>
                </div>

                <div v-else>
                    <div v-if="confirming">
                        <Button
                            class="mr-3"
                            :disabled="enabling"
                            @click="confirmTwoFactorAuthentication"
                        >
                            Confirm
                        </Button>

                        <Button
                            variant="secondary"
                            :disabled="disabling"
                            @click="disableTwoFactorAuthentication"
                        >
                            Cancel
                        </Button>
                    </div>

                    <div v-else>
                        <Button
                            v-if="recoveryCodes.length > 0"
                            class="mr-3"
                            variant="secondary"
                            @click="regenerateRecoveryCodes"
                        >
                            Regenerate Recovery Codes
                        </Button>

                        <Button
                            v-if="recoveryCodes.length === 0"
                            class="mr-3"
                            variant="secondary"
                            @click="showRecoveryCodes"
                        >
                            Show Recovery Codes
                        </Button>

                        <Button
                            variant="destructive"
                            :disabled="disabling"
                            @click="disableTwoFactorAuthentication"
                        >
                            Disable
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
