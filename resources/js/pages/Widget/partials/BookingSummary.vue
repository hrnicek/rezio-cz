<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import { StickyNote } from "lucide-vue-next";
import { useCurrency } from "@/composables/useCurrency";

import type { CalendarService } from "../types";

const { formatCurrency } = useCurrency();

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
        <div class="pb-4 border-b border-gray-100">
          <div class="flex justify-between text-gray-500 mb-1">
            <span>Termín pobytu</span>
          </div>
          <div class="font-medium text-lg text-gray-900">
             <div v-if="startDate">{{ calendar.formatDate(startDate) }}</div>
             <div v-if="endDate" class="text-gray-400 text-xs">—</div>
             <div v-if="endDate">{{ calendar.formatDate(endDate) }}</div>
             <div v-if="!startDate" class="text-gray-400 italic text-sm">Vyberte termín pobytu</div>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between text-gray-600">
            <span>Počet nocí</span>
            <span class="font-medium text-gray-900">{{ selectedNights }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Cena ubytování</span>
            <span class="font-medium text-gray-900">{{ formatCurrency(selectedTotalPrice) }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Sezóna</span>
            <span class="font-medium text-gray-900">{{ seasonLabel }}</span>
          </div>
          <div v-if="addonsTotalPrice > 0" class="flex justify-between text-gray-600">
            <span>Služby</span>
            <span class="font-medium text-gray-900">{{ formatCurrency(addonsTotalPrice) }}</span>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4 mt-4">
          <div class="flex items-end justify-between">
            <span class="font-medium text-gray-900">Celkem</span>
            <span class="text-xl font-bold text-primary">{{ formatCurrency(grandTotalPrice) }}</span>
          </div>
        </div>
      </CardContent>
    </Card>
    
    <div class="mt-6 text-xs text-gray-400 px-2">
       <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-primary"></span> Vybráno</div>
       <div class="flex items-center gap-2 mb-2"><span class="h-2 w-2 rounded-full bg-red-300"></span> Obsazeno</div>
       <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-gray-200"></span> Nedostupné</div>
    </div>
  </div>
</template>
