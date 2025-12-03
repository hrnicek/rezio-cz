<script setup lang="ts">
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";

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
        
        <!-- Company Billing Toggle -->
        <div class="md:col-span-2 flex items-center space-x-2 mt-2">
            <Checkbox 
                id="is_company" 
                :checked="customer.isCompany" 
                @update:checked="(v) => customer.isCompany = v" 
            />
            <Label for="is_company" class="cursor-pointer">Nakupuji na firmu</Label>
        </div>

        <!-- Company & Billing Fields -->
        <template v-if="customer.isCompany">
            <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">Název firmy</Label>
                <Input 
                    v-model="customer.companyName"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.companyName}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.companyName }}</span>
            </div>
            <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">IČO</Label>
                <Input 
                    v-model="customer.ico"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.ico}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.ico }}</span>
            </div>
            <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">DIČ</Label>
                <Input 
                    v-model="customer.dic"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.dic}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.dic }}</span>
            </div>
             <div class="flex items-center space-x-2 mt-8">
                <Checkbox 
                    id="has_vat" 
                    :checked="customer.hasVat" 
                    @update:checked="(v) => customer.hasVat = v" 
                />
                <Label for="has_vat" class="cursor-pointer">Plátce DPH</Label>
            </div>
            
            <div class="md:col-span-2 border-t pt-4 mt-2">
                <h3 class="text-sm font-medium text-muted-foreground mb-4">Fakturační adresa</h3>
            </div>

            <div class="md:col-span-2 space-y-1">
                <Label class="text-sm text-muted-foreground">Ulice a číslo</Label>
                <Input 
                    v-model="customer.billingStreet"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.billingStreet}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.billingStreet }}</span>
            </div>
            <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">Město</Label>
                <Input 
                    v-model="customer.billingCity"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.billingCity}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.billingCity }}</span>
            </div>
            <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">PSČ</Label>
                <Input 
                    v-model="customer.billingZip"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.billingZip}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.billingZip }}</span>
            </div>
             <div class="space-y-1">
                <Label class="text-sm text-muted-foreground">Země</Label>
                <Input 
                    v-model="customer.billingCountry"
                    :class="{'border-destructive focus-visible:ring-destructive': fieldErrors.billingCountry}"
                />
                <span class="text-xs text-destructive min-h-[16px] block">{{ fieldErrors.billingCountry }}</span>
            </div>
        </template>

        <div class="md:col-span-2 space-y-1">
           <Label class="text-sm text-muted-foreground">Poznámka</Label>
           <Textarea v-model="customer.note" rows="3" />
        </div>
     </div>
  </div>
</template>
