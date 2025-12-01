<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { watch } from 'vue'

export interface CustomerFormData {
    id?: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    
    is_company: boolean;
    company_name: string | null;
    ico: string | null;
    dic: string | null;
    has_vat: boolean;

    billing_street: string | null;
    billing_city: string | null;
    billing_zip: string | null;
    billing_country: string | null;

    internal_notes: string | null;
    is_registered: boolean;
}

const props = defineProps<{
  customer?: CustomerFormData
}>()

const emit = defineEmits(['success'])

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  
  is_company: false,
  company_name: '',
  ico: '',
  dic: '',
  has_vat: false,

  billing_street: '',
  billing_city: '',
  billing_zip: '',
  billing_country: '',
  
  internal_notes: '',
  is_registered: false,
})

watch(() => props.customer, (newCustomer) => {
  if (newCustomer) {
    form.first_name = newCustomer.first_name
    form.last_name = newCustomer.last_name
    form.email = newCustomer.email
    form.phone = newCustomer.phone ?? ''
    
    form.is_company = newCustomer.is_company
    form.company_name = newCustomer.company_name ?? ''
    form.ico = newCustomer.ico ?? ''
    form.dic = newCustomer.dic ?? ''
    form.has_vat = newCustomer.has_vat

    form.billing_street = newCustomer.billing_street ?? ''
    form.billing_city = newCustomer.billing_city ?? ''
    form.billing_zip = newCustomer.billing_zip ?? ''
    form.billing_country = newCustomer.billing_country ?? ''
    
    form.internal_notes = newCustomer.internal_notes ?? ''
    form.is_registered = newCustomer.is_registered
  } else {
    form.reset()
    form.is_company = false
    form.has_vat = false
    form.is_registered = false
  }
}, { immediate: true })

const submit = () => {
  if (props.customer?.id) {
    form.put(route('admin.customers.update', props.customer.id), {
      onSuccess: () => emit('success'),
    })
  } else {
    form.post(route('admin.customers.store'), {
      onSuccess: () => {
        form.reset()
        emit('success')
      },
    })
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6 py-4">
    <!-- Osobní údaje -->
    <div class="space-y-4">
        <h3 class="text-sm font-medium text-muted-foreground uppercase tracking-wider border-b pb-2">Osobní údaje</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="first_name">Jméno</Label>
                <Input id="first_name" v-model="form.first_name" class="h-9" />
                <p v-if="form.errors.first_name" class="text-sm text-red-500">{{ form.errors.first_name }}</p>
            </div>
            <div class="space-y-2">
                <Label for="last_name">Příjmení</Label>
                <Input id="last_name" v-model="form.last_name" class="h-9" />
                <p v-if="form.errors.last_name" class="text-sm text-red-500">{{ form.errors.last_name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" v-model="form.email" class="h-9" />
                <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
            </div>
            <div class="space-y-2">
                <Label for="phone">Telefon</Label>
                <Input id="phone" v-model="form.phone" class="h-9" />
                <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
            </div>
        </div>
    </div>

    <!-- Firemní údaje -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b pb-2">
             <h3 class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Firemní údaje</h3>
             <div class="flex items-center space-x-2">
                <Checkbox id="is_company" :checked="form.is_company" @update:checked="form.is_company = $event" />
                <Label for="is_company" class="font-normal cursor-pointer">Je firma?</Label>
            </div>
        </div>

        <div v-if="form.is_company" class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="space-y-2">
                <Label for="company_name">Název firmy</Label>
                <Input id="company_name" v-model="form.company_name" class="h-9" />
                <p v-if="form.errors.company_name" class="text-sm text-red-500">{{ form.errors.company_name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="ico">IČO</Label>
                    <Input id="ico" v-model="form.ico" class="h-9" />
                    <p v-if="form.errors.ico" class="text-sm text-red-500">{{ form.errors.ico }}</p>
                </div>
                <div class="space-y-2">
                    <Label for="dic">DIČ</Label>
                    <Input id="dic" v-model="form.dic" class="h-9" />
                    <p v-if="form.errors.dic" class="text-sm text-red-500">{{ form.errors.dic }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <Checkbox id="has_vat" :checked="form.has_vat" @update:checked="form.has_vat = $event" />
                <Label for="has_vat" class="font-normal cursor-pointer">Plátce DPH</Label>
            </div>
        </div>
    </div>

    <!-- Fakturační adresa -->
    <div class="space-y-4">
        <h3 class="text-sm font-medium text-muted-foreground uppercase tracking-wider border-b pb-2">Fakturační adresa</h3>
        
        <div class="space-y-2">
            <Label for="billing_street">Ulice a číslo</Label>
            <Input id="billing_street" v-model="form.billing_street" class="h-9" />
            <p v-if="form.errors.billing_street" class="text-sm text-red-500">{{ form.errors.billing_street }}</p>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="space-y-2">
                <Label for="billing_city">Město</Label>
                <Input id="billing_city" v-model="form.billing_city" class="h-9" />
                <p v-if="form.errors.billing_city" class="text-sm text-red-500">{{ form.errors.billing_city }}</p>
            </div>
            <div class="space-y-2">
                <Label for="billing_zip">PSČ</Label>
                <Input id="billing_zip" v-model="form.billing_zip" class="h-9" />
                <p v-if="form.errors.billing_zip" class="text-sm text-red-500">{{ form.errors.billing_zip }}</p>
            </div>
            <div class="space-y-2">
                <Label for="billing_country">Země</Label>
                <Input id="billing_country" v-model="form.billing_country" class="h-9" />
                <p v-if="form.errors.billing_country" class="text-sm text-red-500">{{ form.errors.billing_country }}</p>
            </div>
        </div>
    </div>

    <!-- Ostatní -->
    <div class="space-y-4">
        <h3 class="text-sm font-medium text-muted-foreground uppercase tracking-wider border-b pb-2">Ostatní</h3>
        
        <div class="space-y-2">
            <Label for="internal_notes">Interní poznámka</Label>
            <Textarea id="internal_notes" v-model="form.internal_notes" rows="3" />
            <p v-if="form.errors.internal_notes" class="text-sm text-red-500">{{ form.errors.internal_notes }}</p>
        </div>

        <div class="flex items-center space-x-2">
            <Checkbox id="is_registered" :checked="form.is_registered" @update:checked="form.is_registered = $event" />
            <Label for="is_registered" class="font-normal cursor-pointer">Registrovaný uživatel (má přístup do systému)</Label>
        </div>
    </div>

    <div class="flex justify-end pt-4 border-t mt-6">
      <Button type="submit" :disabled="form.processing" class="h-9">
        {{ props.customer ? 'Upravit zákazníka' : 'Vytvořit zákazníka' }}
      </Button>
    </div>
  </form>
</template>
