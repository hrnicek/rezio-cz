<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import { StickyNote } from "lucide-vue-next";
import { useCurrency } from "@/composables/useCurrency";

import type { CalendarService } from "../types";

const { formatPrice } = useCurrency();

defineProps<{
  calendar: CalendarService;
  startDate: string | null;
  endDate: string | null;
  selectedNights: number;
  selectedTotalPrice: number;
  seasonLabel: string;
  addonsTotalPrice: number;
  grandTotalPrice: number;
}>();
</script>

<template>
  <div class="sticky top-8">
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <StickyNote class="h-5 w-5 text-primary" />
          Rezervace
        </CardTitle>
        <CardDescription>Souhrn vašeho pobytu</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4 text-sm">
        <div class="pb-4 border-b border-border">
          <div class="flex justify-between text-muted-foreground mb-1">
            <span>Termín pobytu</span>
          </div>
          <div class="font-medium text-lg text-foreground">
             <div v-if="startDate">{{ calendar.formatDate(startDate) }}</div>
             <div v-if="endDate" class="text-muted-foreground text-xs">—</div>
             <div v-if="endDate">{{ calendar.formatDate(endDate) }}</div>
             <div v-if="!startDate" class="text-muted-foreground italic text-sm">Vyberte termín pobytu</div>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between text-muted-foreground">
            <span>Počet nocí</span>
            <span class="font-medium text-foreground">{{ selectedNights }}</span>
          </div>
          <div class="flex justify-between text-muted-foreground">
            <span>Cena ubytování</span>
            <span class="font-medium text-foreground">{{ formatPrice(selectedTotalPrice) }}</span>
          </div>
          <div class="flex justify-between text-muted-foreground">
            <span>Sezóna</span>
            <span class="font-medium text-foreground">{{ seasonLabel }}</span>
          </div>
          <div v-if="addonsTotalPrice > 0" class="flex justify-between text-muted-foreground">
            <span>Služby</span>
            <span class="font-medium text-foreground">{{ formatPrice(addonsTotalPrice) }}</span>
          </div>
        </div>

        <div class="border-t border-border pt-4 mt-4">
          <div class="flex items-end justify-between">
            <span class="font-medium text-foreground">Celkem</span>
            <span class="text-xl font-bold text-primary">{{ formatPrice(grandTotalPrice) }}</span>
          </div>
        </div>
      </CardContent>
    </Card>
    
    <div class="mt-6 text-xs text-muted-foreground px-2">
       <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-primary"></span> Vybráno</div>
       <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-destructive/50"></span> Obsazeno</div>
       <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-muted"></span> Nedostupné</div>
    </div>
  </div>
</template>
