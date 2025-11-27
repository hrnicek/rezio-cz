<script setup lang="ts">
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";

import type { Customer } from "../types";

defineProps<{
  customer: Customer;
  fieldErrors: Record<string, string>;
}>();

const labels: Record<string, string> = {
  firstName: 'Jméno',
  lastName: 'Příjmení',
  email: 'E-mail',
  phone: 'Telefon'
};
const fields: (keyof Pick<Customer, 'firstName' | 'lastName' | 'email' | 'phone'>)[] = ['firstName', 'lastName', 'email', 'phone'];
</script>

<template>
  <div class="space-y-8">
     <h1 class="text-lg font-semibold tracking-tight text-foreground">Kontaktní údaje hosta</h1>
     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="field in fields" :key="field" class="space-y-1">
           <Label class="text-sm text-muted-foreground capitalize">
              {{ labels[field] }}
           </Label>
           <Input 
              v-model="customer[field]" 
              :class="{'border-destructive focus-visible:ring-destructive': fieldErrors[field]}"
           />
           <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors[field] }}</span>
        </div>
        <div class="md:col-span-2 space-y-1">
           <Label class="text-sm text-muted-foreground">Poznámka</Label>
           <Textarea v-model="customer.note" rows="3" />
        </div>
     </div>
  </div>
</template>
