<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';
import { toast } from 'vue-sonner';

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
            toast.success('Dvoufázové ověření bylo úspěšně povoleno.');
        },
        onError: () => {
            toast.error('Zadaný kód je neplatný.');
        }
    });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes')).then(() => {
        showRecoveryCodes();
        toast.success('Obnovovací kódy byly přegenerovány.');
    });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
            toast.success('Dvoufázové ověření bylo zakázáno.');
        },
    });
};

declare const route: any;
</script>

<template>
    <SettingsLayout>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-foreground">Dvoufázové ověření</h3>
                <p class="mt-1 text-sm text-muted-foreground">Zvyšte bezpečnost svého účtu pomocí dvoufázového ověření.</p>
            </div>

            <div v-if="twoFactorEnabled && !confirming">
                <h3 class="text-lg font-medium text-foreground">
                    Máte zapnuté dvoufázové ověření.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-muted-foreground">
                    Když je zapnuté dvoufázové ověření, budete při přihlašování vyzváni k zadání bezpečného náhodného tokenu. Tento token můžete získat z aplikace Google Authenticator ve vašem telefonu.
                </p>
            </div>

            <div v-else-if="twoFactorEnabled && confirming">
                <h3 class="text-lg font-medium text-foreground">
                    Dokončete nastavení dvoufázového ověření.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-muted-foreground">
                    Pro dokončení nastavení dvoufázového ověření naskenujte následující QR kód pomocí aplikace Google Authenticator nebo zadejte nastavovací klíč a vložte vygenerovaný OTP kód.
                </p>
            </div>

            <div v-else>
                <h3 class="text-lg font-medium text-foreground">
                    Nemáte zapnuté dvoufázové ověření.
                </h3>

                <p class="mt-3 max-w-xl text-sm text-muted-foreground">
                    Když je zapnuté dvoufázové ověření, budete při přihlašování vyzváni k zadání bezpečného náhodného tokenu. Tento token můžete získat z aplikace Google Authenticator ve vašem telefonu.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 p-2 inline-block bg-white">
                        <div v-html="qrCode" />
                    </div>

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-muted-foreground">
                        <p class="font-semibold">
                            Nastavovací klíč: <span v-html="setupKey"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <Label for="code">Kód</Label>
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
                    <div class="mt-4 max-w-xl text-sm text-muted-foreground">
                        <p class="font-semibold">
                            Uložte tyto obnovovací kódy do bezpečného správce hesel. Mohou být použity k obnovení přístupu k vašemu účtu, pokud ztratíte zařízení pro dvoufázové ověření.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-muted rounded-md">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="!twoFactorEnabled">
                    <Button :disabled="enabling" @click="enableTwoFactorAuthentication">
                        Zapnout
                    </Button>
                </div>

                <div v-else>
                    <div v-if="confirming">
                        <Button
                            class="mr-3"
                            :disabled="enabling"
                            @click="confirmTwoFactorAuthentication"
                        >
                            Potvrdit
                        </Button>

                        <Button
                            variant="secondary"
                            :disabled="disabling"
                            @click="disableTwoFactorAuthentication"
                        >
                            Zrušit
                        </Button>
                    </div>

                    <div v-else>
                        <Button
                            v-if="recoveryCodes.length > 0"
                            class="mr-3"
                            variant="secondary"
                            @click="regenerateRecoveryCodes"
                        >
                            Vygenerovat nové obnovovací kódy
                        </Button>

                        <Button
                            v-if="recoveryCodes.length === 0"
                            class="mr-3"
                            variant="secondary"
                            @click="showRecoveryCodes"
                        >
                            Zobrazit obnovovací kódy
                        </Button>

                        <Button
                            variant="destructive"
                            :disabled="disabling"
                            @click="disableTwoFactorAuthentication"
                        >
                            Vypnout
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
