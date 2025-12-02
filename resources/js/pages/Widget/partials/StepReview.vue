<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { Check } from "lucide-vue-next";
import { Calendar, User, PawPrint } from "lucide-vue-next";
import { useCurrency } from "@/composables/useCurrency";
import { ServicePriceType } from "@/lib/enums";

import type { Customer, ExtraService, CalendarService } from "../types";

const { formatCurrency } = useCurrency();

defineProps<{
  customer: Customer;
  startDate: string | null;
  endDate: string | null;
  selectedNights: number;
  selectedExtras: ExtraService[];
  extraSelection: Record<number, number>;
  grandTotalPrice: number;
  calendar: CalendarService;
  agreeGdpr: boolean;
  agreeTerms: boolean;
}>();

const emit = defineEmits<{
  (e: 'navigate', step: number): void;
  (e: 'update:agree-gdpr', val: boolean): void;
  (e: 'update:agree-terms', val: boolean): void;
}>();
</script>

<template>
  <div class="space-y-8">
    <h1 class="text-lg font-semibold tracking-tight text-foreground">Kontrola a souhrn</h1>
    <!-- Summary Card -->
    <div class="bg-card border border-border rounded-md overflow-hidden">
      <!-- Header with Dates -->
      <div class="bg-muted/50 px-6 py-4 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-md bg-background border border-border flex items-center justify-center text-primary">
            <Calendar class="h-5 w-5" />
          </div>
          <div>
            <div class="text-xs text-muted-foreground uppercase tracking-wider font-mono">Termín pobytu</div>
            <div class="font-medium text-foreground">
              {{ calendar.formatDate(startDate) }} — {{ calendar.formatDate(endDate) }}
            </div>
          </div>
        </div>
        <div class="text-right hidden sm:block">
          <div class="text-xs text-muted-foreground uppercase tracking-wider font-mono">Počet nocí</div>
          <div class="font-bold text-foreground">{{ selectedNights }}</div>
        </div>
      </div>
      <div class="p-6 grid gap-8 md:grid-cols-2">
        <!-- Customer Details -->
        <div class="space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-border">
            <h3 class="font-semibold text-foreground flex items-center gap-2">
              <User class="h-4 w-4 text-muted-foreground" /> Kontaktní údaje
            </h3>
            <Button variant="ghost" size="sm" class="h-8 text-xs text-primary hover:text-primary/80" @click="emit('navigate', 2)">Upravit</Button>
          </div>
          <dl class="grid grid-cols-[100px_1fr] gap-y-2 text-sm">
            <dt class="text-muted-foreground">Jméno:</dt>
            <dd class="font-medium text-foreground">{{ customer.firstName }} {{ customer.lastName }}</dd>
            <dt class="text-muted-foreground">Email:</dt>
            <dd class="font-medium text-foreground break-all">{{ customer.email }}</dd>
            <dt class="text-muted-foreground">Telefon:</dt>
            <dd class="font-medium text-foreground">{{ customer.phone }}</dd>
            <dt class="text-muted-foreground">Poznámka:</dt>
            <dd class="text-foreground italic">{{ customer.note || '-' }}</dd>
          </dl>
        </div>
        <!-- Selected Services -->
        <div class="space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-border">
            <h3 class="font-semibold text-foreground flex items-center gap-2">
              <PawPrint class="h-4 w-4 text-muted-foreground" /> Doplňkové služby
            </h3>
            <Button variant="ghost" size="sm" class="h-8 text-xs text-primary hover:text-primary/80" @click="emit('navigate', 3)">Upravit</Button>
          </div>
          <div v-if="selectedExtras.length > 0" class="space-y-3">
            <div v-for="ex in selectedExtras" :key="ex.id" class="flex justify-between items-center text-sm">
              <div class="flex items-center gap-2">
                <span class="bg-muted text-muted-foreground px-2 py-0.5 rounded text-xs font-medium">{{ extraSelection[ex.id] }}x</span>
                <span class="text-foreground">{{ ex.name }}</span>
              </div>
              <span class="font-medium text-foreground">
                {{ formatCurrency((ex.price_type === ServicePriceType.PerNight ? (ex.price.amount * extraSelection[ex.id] * selectedNights) : (ex.price.amount * extraSelection[ex.id])) / 100) }}
              </span>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground italic py-2">
            Nebyly vybrány žádné doplňkové služby.
          </div>
        </div>
      </div>
      <!-- Price Total Footer -->
      <div class="bg-muted/50 p-6 border-t border-border">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div class="space-y-1">
            <div class="text-xs text-muted-foreground uppercase tracking-wider font-mono">Celková cena za pobyt</div>
            <div class="text-xs text-muted-foreground">Včetně DPH a všech poplatků</div>
          </div>
          <div class="text-2xl font-bold text-primary tracking-tight">
            {{ formatCurrency(grandTotalPrice / 100) }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- Terms & Conditions - Native Implementation for Robustness -->
    <div class="space-y-4 pt-4">
      <!-- GDPR -->
      <label class="flex items-start gap-3 p-4 rounded-md border border-border cursor-pointer hover:bg-muted/50 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5 group">
        <div class="relative flex items-center mt-1">
          <input 
            type="checkbox" 
            class="peer sr-only"
            :checked="agreeGdpr"
            @change="emit('update:agree-gdpr', ($event.target as HTMLInputElement).checked)"
          >
          <div class="h-5 w-5 rounded border border-input bg-background peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:bg-primary peer-checked:border-primary transition-all flex items-center justify-center">
            <Check class="h-3.5 w-3.5 text-primary-foreground opacity-0 peer-checked:opacity-100 transition-opacity" />
          </div>
        </div>
        <div class="space-y-1">
          <span class="font-medium text-foreground block group-hover:text-primary transition-colors">
            Souhlasím se zpracováním osobních údajů
          </span>
          <span class="block text-xs text-muted-foreground leading-relaxed font-normal">
            Vaše údaje budou zpracovány v souladu s našimi zásadami ochrany osobních údajů pouze pro účely vyřízení této rezervace.
          </span>
        </div>
      </label>

      <!-- Terms -->
      <label class="flex items-start gap-3 p-4 rounded-md border border-border cursor-pointer hover:bg-muted/50 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5 group">
        <div class="relative flex items-center mt-1">
          <input 
            type="checkbox" 
            class="peer sr-only"
            :checked="agreeTerms"
            @change="emit('update:agree-terms', ($event.target as HTMLInputElement).checked)"
          >
          <div class="h-5 w-5 rounded border border-input bg-background peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:bg-primary peer-checked:border-primary transition-all flex items-center justify-center">
            <Check class="h-3.5 w-3.5 text-primary-foreground opacity-0 peer-checked:opacity-100 transition-opacity" />
          </div>
        </div>
        <div class="space-y-1">
          <span class="font-medium text-foreground block group-hover:text-primary transition-colors">
            Souhlasím s obchodními podmínkami
          </span>
          <span class="block text-xs text-muted-foreground leading-relaxed font-normal">
            Potvrzuji, že jsem se seznámil(a) s ubytovacím řádem a storno podmínkami ubytovatele.
          </span>
        </div>
      </label>
    </div>
  </div>
</template>
