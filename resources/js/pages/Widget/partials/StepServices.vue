<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { Plus, Minus, PawPrint } from "lucide-vue-next";
import { useCurrency } from "@/composables/useCurrency";
import { ServicePriceTypeLabels } from "@/lib/enums";

import type { ExtraService } from "../types";

const { formatCurrency } = useCurrency();

const props = defineProps<{
  validExtras: ExtraService[];
  extraSelection: Record<number, number>;
}>();

const emit = defineEmits<{
  (e: 'update:extraSelection', id: number, value: number): void;
}>();

const updateExtra = (id: number, delta: number) => {
  const current = props.extraSelection[id] || 0;
  const newValue = Math.max(0, current + delta);
  emit('update:extraSelection', id, newValue);
};
</script>

<template>
  <div class="space-y-8">
    <h1 class="text-lg font-semibold tracking-tight text-foreground">Příplatky a služby</h1>
    <div v-if="validExtras.length === 0" class="text-center py-12 text-muted-foreground bg-muted/50 rounded-md">
      <PawPrint class="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
      <p>Pro tento termín nejsou dostupné žádné doplňkové služby.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="extra in validExtras" :key="extra.id" 
           class="bg-card border rounded-md p-4 flex flex-col justify-between transition-all duration-200"
           :class="extraSelection[extra.id] > 0 ? 'border-primary ring-1 ring-primary bg-primary/5' : 'border-border'">
        
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-foreground">{{ extra.name }}</h3>
            <p class="text-sm text-muted-foreground mt-1">{{ extra.description }}</p>
          </div>
          <div class="text-right">
            <div class="font-bold text-primary">{{ formatCurrency(extra.price) }}</div>
            <div class="text-xs text-muted-foreground">{{ ServicePriceTypeLabels[extra.price_type] }}</div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-auto pt-4 border-t border-border">
           <Button variant="outline" size="icon" class="h-9 w-9 rounded-md" 
                   :disabled="!extraSelection[extra.id]"
                   @click="updateExtra(extra.id, -1)">
             <Minus class="h-4 w-4" />
           </Button>
           <span class="font-semibold w-6 text-center text-foreground">{{ extraSelection[extra.id] || 0 }}</span>
           <Button variant="outline" size="icon" class="h-9 w-9 rounded-md hover:bg-primary hover:text-primary-foreground hover:border-primary transition-colors"
                   @click="updateExtra(extra.id, 1)">
             <Plus class="h-4 w-4" />
           </Button>
        </div>
      </div>
    </div>
  </div>
</template>
