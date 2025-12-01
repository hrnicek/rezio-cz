<script setup lang="ts">
import PropertyLayout from '../Partials/PropertyLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import axios from 'axios';
import { Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

declare const route: any;

const props = defineProps<{
    property: any;
    billingSetting: any;
}>();

const isLoadingAres = ref(false);

const fetchAresData = async () => {
    if (!form.ico) {
        toast.error('Zadejte IČO.');
        return;
    }

    isLoadingAres.value = true;
    try {
        const response = await axios.get(route('api.ares.show', form.ico));
        const data = response.data;

        form.company_name = data.company_name;
        form.street = data.street;
        form.city = data.city;
        form.zip = data.zip;
        form.dic = data.dic;
        form.is_vat_payer = data.is_vat_payer;
        form.country = data.country;
        
        toast.success('Údaje byly načteny z ARES.');
    } catch (error: any) {
        if (error.response?.status === 404) {
            toast.error('IČO nebylo nalezeno v ARES.');
        } else {
            toast.error('Chyba při komunikaci s ARES.');
        }
    } finally {
        isLoadingAres.value = false;
    }
};

const form = useForm({
    is_vat_payer: props.billingSetting.is_vat_payer ?? false,
    ico: props.billingSetting.ico ?? '',
    dic: props.billingSetting.dic ?? '',
    company_name: props.billingSetting.company_name ?? '',
    street: props.billingSetting.street ?? '',
    city: props.billingSetting.city ?? '',
    zip: props.billingSetting.zip ?? '',
    country: props.billingSetting.country ?? '',
    default_note: props.billingSetting.default_note ?? '',
    
    bank_account: props.billingSetting.bank_account ?? '',
    iban: props.billingSetting.iban ?? '',
    swift: props.billingSetting.swift ?? '',
    currency: props.billingSetting.currency ?? 'CZK',
    show_bank_account: props.billingSetting.show_bank_account ?? true,

    proforma_prefix: props.billingSetting.proforma_prefix ?? '',
    proforma_current_sequence: props.billingSetting.proforma_current_sequence ?? 1,
    invoice_prefix: props.billingSetting.invoice_prefix ?? '',
    invoice_current_sequence: props.billingSetting.invoice_current_sequence ?? 1,
    receipt_prefix: props.billingSetting.receipt_prefix ?? '',
    receipt_current_sequence: props.billingSetting.receipt_current_sequence ?? 1,
    
    due_days: props.billingSetting.due_days ?? 14,
});

const submit = () => {
    form.put(route('admin.properties.billing.update', props.property.id), {
        onSuccess: () => {
            toast.success('Nastavení fakturace bylo uloženo.');
        },
        onError: () => {
            toast.error('Nepodařilo se uložit nastavení.');
        }
    });
};

const breadcrumbs = [
    { title: 'Nemovitosti', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Fakturace', href: `/admin/properties/${props.property.id}/billing` },
];
</script>

<template>
    <Head title="Nastavení fakturace" />

    <PropertyLayout :property="property" :breadcrumbs="breadcrumbs">
        <div class="space-y-6 max-w-4xl">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Fakturace</h2>
                <p class="text-muted-foreground">Nastavení fakturačních údajů a číslování dokladů.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Identity & Tax -->
                <Card class="border shadow-none">
                    <CardHeader>
                        <CardTitle>Fakturační údaje</CardTitle>
                        <CardDescription>Údaje o dodavateli zobrazované na fakturách.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="company_name">Název firmy / Jméno</Label>
                                <Input id="company_name" v-model="form.company_name" class="h-9" />
                                <div v-if="form.errors.company_name" class="text-sm text-destructive">{{ form.errors.company_name }}</div>
                            </div>
                            <div class="space-y-2">
                                <Label for="ico">IČO</Label>
                                <div class="flex gap-2">
                                    <Input id="ico" v-model="form.ico" class="h-9" placeholder="IČO" />
                                    <Button type="button" variant="outline" size="sm" @click="fetchAresData" :disabled="isLoadingAres" class="h-9 px-3">
                                        <Loader2 v-if="isLoadingAres" class="h-4 w-4 animate-spin" />
                                        <span v-else>Načíst z ARES</span>
                                    </Button>
                                </div>
                                <div v-if="form.errors.ico" class="text-sm text-destructive">{{ form.errors.ico }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="dic">DIČ</Label>
                                <Input id="dic" v-model="form.dic" class="h-9" />
                                <div v-if="form.errors.dic" class="text-sm text-destructive">{{ form.errors.dic }}</div>
                            </div>
                             <div class="flex items-center space-x-2 pt-8">
                                <Switch id="is_vat_payer" :checked="form.is_vat_payer" @update:checked="form.is_vat_payer = $event" />
                                <Label for="is_vat_payer">Plátce DPH</Label>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="street">Ulice a číslo</Label>
                            <Input id="street" v-model="form.street" class="h-9" />
                             <div v-if="form.errors.street" class="text-sm text-destructive">{{ form.errors.street }}</div>
                        </div>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="city">Město</Label>
                                <Input id="city" v-model="form.city" class="h-9" />
                                 <div v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</div>
                            </div>
                             <div class="space-y-2">
                                <Label for="zip">PSČ</Label>
                                <Input id="zip" v-model="form.zip" class="h-9" />
                                 <div v-if="form.errors.zip" class="text-sm text-destructive">{{ form.errors.zip }}</div>
                            </div>
                             <div class="space-y-2">
                                <Label for="country">Země</Label>
                                <Input id="country" v-model="form.country" class="h-9" />
                                 <div v-if="form.errors.country" class="text-sm text-destructive">{{ form.errors.country }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Banking -->
                <Card class="border shadow-none">
                    <CardHeader>
                        <CardTitle>Bankovní spojení</CardTitle>
                        <CardDescription>Účet pro platby převodem.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="bank_account">Číslo účtu</Label>
                                <Input id="bank_account" v-model="form.bank_account" class="h-9" />
                                 <div v-if="form.errors.bank_account" class="text-sm text-destructive">{{ form.errors.bank_account }}</div>
                            </div>
                             <div class="space-y-2">
                                <Label for="currency">Měna</Label>
                                <Input id="currency" v-model="form.currency" class="h-9" maxlength="3" />
                                 <div v-if="form.errors.currency" class="text-sm text-destructive">{{ form.errors.currency }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="iban">IBAN</Label>
                                <Input id="iban" v-model="form.iban" class="h-9" />
                                 <div v-if="form.errors.iban" class="text-sm text-destructive">{{ form.errors.iban }}</div>
                            </div>
                             <div class="space-y-2">
                                <Label for="swift">SWIFT / BIC</Label>
                                <Input id="swift" v-model="form.swift" class="h-9" />
                                 <div v-if="form.errors.swift" class="text-sm text-destructive">{{ form.errors.swift }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Switch id="show_bank_account" :checked="form.show_bank_account" @update:checked="form.show_bank_account = $event" />
                            <Label for="show_bank_account">Zobrazovat účet na dokladech</Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Numbering -->
                <Card class="border shadow-none">
                    <CardHeader>
                        <CardTitle>Číslování dokladů</CardTitle>
                        <CardDescription>Nastavení prefixů a číselných řad.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                             <!-- Proforma -->
                             <div class="space-y-2 border p-3 rounded-md">
                                <h4 class="text-sm font-medium mb-2">Zálohové faktury</h4>
                                <div class="space-y-2">
                                    <Label for="proforma_prefix" class="text-xs text-muted-foreground">Prefix</Label>
                                    <Input id="proforma_prefix" v-model="form.proforma_prefix" class="h-8" placeholder="ZAL-" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="proforma_current_sequence" class="text-xs text-muted-foreground">Aktuální číslo</Label>
                                    <Input id="proforma_current_sequence" type="number" v-model="form.proforma_current_sequence" class="h-8" />
                                </div>
                             </div>

                             <!-- Invoice -->
                             <div class="space-y-2 border p-3 rounded-md">
                                <h4 class="text-sm font-medium mb-2">Faktury</h4>
                                <div class="space-y-2">
                                    <Label for="invoice_prefix" class="text-xs text-muted-foreground">Prefix</Label>
                                    <Input id="invoice_prefix" v-model="form.invoice_prefix" class="h-8" placeholder="FA-" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="invoice_current_sequence" class="text-xs text-muted-foreground">Aktuální číslo</Label>
                                    <Input id="invoice_current_sequence" type="number" v-model="form.invoice_current_sequence" class="h-8" />
                                </div>
                             </div>

                             <!-- Receipt -->
                             <div class="space-y-2 border p-3 rounded-md">
                                <h4 class="text-sm font-medium mb-2">Příjmové doklady</h4>
                                <div class="space-y-2">
                                    <Label for="receipt_prefix" class="text-xs text-muted-foreground">Prefix</Label>
                                    <Input id="receipt_prefix" v-model="form.receipt_prefix" class="h-8" placeholder="PD-" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="receipt_current_sequence" class="text-xs text-muted-foreground">Aktuální číslo</Label>
                                    <Input id="receipt_current_sequence" type="number" v-model="form.receipt_current_sequence" class="h-8" />
                                </div>
                             </div>
                        </div>
                    </CardContent>
                </Card>
                
                 <!-- Other -->
                <Card class="border shadow-none">
                    <CardHeader>
                        <CardTitle>Ostatní nastavení</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                             <div class="space-y-2">
                                <Label for="due_days">Splatnost (dny)</Label>
                                <Input id="due_days" type="number" v-model="form.due_days" class="h-9" />
                                 <div v-if="form.errors.due_days" class="text-sm text-destructive">{{ form.errors.due_days }}</div>
                            </div>
                        </div>
                         <div class="space-y-2">
                            <Label for="default_note">Výchozí poznámka na dokladech</Label>
                            <Textarea id="default_note" v-model="form.default_note" class="min-h-[80px]" />
                             <div v-if="form.errors.default_note" class="text-sm text-destructive">{{ form.errors.default_note }}</div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing" class="h-9 shadow-sm">
                        Uložit nastavení
                    </Button>
                </div>
            </form>
        </div>
    </PropertyLayout>
</template>
