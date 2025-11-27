<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { watch } from 'vue'

const props = defineProps<{
  customer?: any
}>()

const emit = defineEmits(['success'])

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  zip: '',
  country: '',
  company_name: '',
  vat_id: '',
  notes: '',
  status: 'active',
})

watch(() => props.customer, (newCustomer) => {
  if (newCustomer) {
    form.first_name = newCustomer.first_name
    form.last_name = newCustomer.last_name
    form.email = newCustomer.email
    form.phone = newCustomer.phone
    form.address = newCustomer.address
    form.city = newCustomer.city
    form.zip = newCustomer.zip
    form.country = newCustomer.country
    form.company_name = newCustomer.company_name
    form.vat_id = newCustomer.vat_id
    form.notes = newCustomer.notes
    form.status = newCustomer.status
  } else {
    form.reset()
    form.status = 'active'
  }
}, { immediate: true })

const submit = () => {
  if (props.customer) {
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
  <form @submit.prevent="submit" class="space-y-4 py-4">
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="first_name">Jméno</Label>
        <Input id="first_name" v-model="form.first_name" />
        <p v-if="form.errors.first_name" class="text-sm text-destructive">{{ form.errors.first_name }}</p>
      </div>
      <div class="space-y-2">
        <Label for="last_name">Příjmení</Label>
        <Input id="last_name" v-model="form.last_name" />
        <p v-if="form.errors.last_name" class="text-sm text-destructive">{{ form.errors.last_name }}</p>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="status">Stav</Label>
        <Select v-model="form.status">
          <SelectTrigger>
            <SelectValue placeholder="Vyberte stav" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="active">Aktivní</SelectItem>
            <SelectItem value="inactive">Neaktivní</SelectItem>
            <SelectItem value="vip">VIP</SelectItem>
            <SelectItem value="blacklisted">Blacklist</SelectItem>
          </SelectContent>
        </Select>
        <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="email">Email</Label>
        <Input id="email" type="email" v-model="form.email" />
        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
      </div>
      <div class="space-y-2">
        <Label for="phone">Telefon</Label>
        <Input id="phone" v-model="form.phone" />
        <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="company_name">Firma</Label>
        <Input id="company_name" v-model="form.company_name" />
      </div>
      <div class="space-y-2">
        <Label for="vat_id">IČ/DIČ</Label>
        <Input id="vat_id" v-model="form.vat_id" />
      </div>
    </div>

    <div class="space-y-2">
      <Label for="address">Adresa</Label>
      <Input id="address" v-model="form.address" />
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div class="space-y-2">
        <Label for="city">Město</Label>
        <Input id="city" v-model="form.city" />
      </div>
      <div class="space-y-2">
        <Label for="zip">PSČ</Label>
        <Input id="zip" v-model="form.zip" />
      </div>
      <div class="space-y-2">
        <Label for="country">Země</Label>
        <Input id="country" v-model="form.country" />
      </div>
    </div>

    <div class="space-y-2">
      <Label for="notes">Poznámka</Label>
      <Textarea id="notes" v-model="form.notes" />
    </div>

    <div class="flex justify-end pt-4">
      <Button type="submit" :disabled="form.processing">
        {{ props.customer ? 'Upravit zákazníka' : 'Vytvořit zákazníka' }}
      </Button>
    </div>
  </form>
</template>
