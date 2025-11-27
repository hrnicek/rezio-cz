<script setup lang="ts">
import { ref } from "vue";
import { ChevronLeft, ChevronRight, Loader2 } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { useCurrency } from "@/composables/useCurrency";

import type { CalendarService } from "../types";

const { formatCurrency } = useCurrency();

const props = defineProps<{
  calendar: CalendarService;
  startDate: string | null;
  endDate: string | null;
  dateSelectionHint: string;
}>();

const emit = defineEmits<{
  (e: 'update:startDate', value: string | null): void;
  (e: 'update:endDate', value: string | null): void;
}>();

const hoverDate = ref<string | null>(null);
const WEEK_DAYS = ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"];

const getCellStyles = (dateStr: string) => {
  const info = props.calendar.getDayInfo(dateStr);
  const isAvailable = info?.available !== false; 
  const isBlackout = !!info?.blackout;

  if (isBlackout) return 'bg-muted text-muted-foreground/50 cursor-not-allowed';
  if (!isAvailable) return 'bg-destructive/10 text-destructive cursor-not-allowed';

  const d = props.calendar.parseISO(dateStr);
  const s = props.startDate ? props.calendar.parseISO(props.startDate) : null;
  const e = props.endDate ? props.calendar.parseISO(props.endDate) : null;
  const h = hoverDate.value ? props.calendar.parseISO(hoverDate.value) : null;

  if (s && d.getTime() === s.getTime()) return 'bg-primary text-primary-foreground font-medium';
  if (e && d.getTime() === e.getTime()) return 'bg-primary text-primary-foreground font-medium';
  if (s && e && d > s && d < e) return 'bg-primary/10 text-primary border-primary/20';
  if (s && !e && h) {
    const min = Math.min(s.getTime(), h.getTime());
    const max = Math.max(s.getTime(), h.getTime());
    if (d.getTime() >= min && d.getTime() <= max) return 'bg-primary/5 text-primary border border-dashed border-primary';
  }

  return 'bg-background hover:bg-muted/50 text-foreground border-border';
};

const handleDateClick = (date: string) => {
  const info = props.calendar.getDayInfo(date);
  if (info?.blackout || info?.available === false) return;

  if (!props.startDate || (props.startDate && props.endDate)) {
    emit('update:startDate', date);
    emit('update:endDate', null);
  } else {
    if (new Date(date) < new Date(props.startDate)) {
       emit('update:endDate', props.startDate);
       emit('update:startDate', date);
    } else {
       emit('update:endDate', date);
    }
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-semibold tracking-tight text-foreground transition-all duration-300">{{ dateSelectionHint }}</h1>
      <div class="flex items-center gap-1 bg-background border border-border rounded-md">
         <Button variant="ghost" size="icon" @click="calendar.changeMonth(-1)" class="h-9 w-9 text-muted-foreground hover:text-primary">
           <ChevronLeft class="h-4 w-4" />
         </Button>
         <span class="w-32 text-center text-sm font-semibold text-foreground select-none">
            {{ calendar.monthLabel.value }} {{ calendar.year.value }}
         </span>
         <Button variant="ghost" size="icon" @click="calendar.changeMonth(1)" class="h-9 w-9 text-muted-foreground hover:text-primary">
           <ChevronRight class="h-4 w-4" />
         </Button>
      </div>
    </div>

    <div class="border border-border rounded-md overflow-hidden select-none">
       <div class="grid grid-cols-7 bg-muted/50 border-b border-border">
          <div v-for="d in WEEK_DAYS" :key="d" class="py-2 text-center text-xs font-semibold text-muted-foreground">
            {{ d }}
          </div>
       </div>
       
       <div class="grid grid-cols-7 bg-background relative">
          <div v-if="calendar.loading.value" class="absolute inset-0 bg-background/80 z-10 flex items-center justify-center">
             <Loader2 class="h-6 w-6 animate-spin text-primary" />
          </div>

          <button
              v-for="(cell, idx) in calendar.cells.value"
              :key="idx"
              @click="handleDateClick(cell.date)"
              @mouseenter="hoverDate = cell.date"
              @mouseleave="hoverDate = null"
              class="h-20 sm:h-24 border-r border-b border-border p-2 flex flex-col justify-between transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary"
              :class="[
                getCellStyles(cell.date), 
                (idx + 1) % 7 === 0 ? 'border-r-0' : '' 
              ]"
          >
              <span class="text-sm font-medium self-end" :class="{'opacity-25': !cell.inCurrent}">{{ cell.day }}</span>
              <span v-if="calendar.getDayInfo(cell.date)?.price" class="text-xs font-medium self-start">
                 {{ formatCurrency(calendar.getDayInfo(cell.date).price) }}
              </span>
          </button>
       </div>
    </div>
  </div>
</template>
