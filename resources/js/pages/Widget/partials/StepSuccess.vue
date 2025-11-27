<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { CheckCircle, Calendar, User } from "lucide-vue-next";

import type { Customer, CalendarService } from "../types";

defineProps<{
  customer: Customer;
  startDate: string | null;
  endDate: string | null;
  calendar: CalendarService;
}>();

declare const route: any;
</script>

<template>
  <div class="py-12 lg:py-20">
    <div class="max-w-xl mx-auto text-center space-y-8">
      <!-- Success Animation Icon -->
      <div class="relative inline-flex items-center justify-center">
        <div class="absolute inset-0 bg-primary/10 rounded-md"></div>
        <div class="relative h-24 w-24 bg-background rounded-md flex items-center justify-center border border-primary/20">
          <CheckCircle class="h-10 w-10 text-primary" />
        </div>
      </div>
      <div class="space-y-4">
        <h2 class="text-xl font-semibold text-foreground tracking-tight">Rezervace odeslána!</h2>
        <p class="text-lg text-muted-foreground max-w-sm mx-auto">
          Děkujeme, <strong>{{ customer.firstName }}</strong>. Potvrzení a další instrukce jsme právě odeslali na Váš email.
        </p>
      </div>
      <!-- Reservation Recap Card -->
      <div class="bg-card rounded-md border border-border p-6 text-left max-w-sm mx-auto transform transition-all">
        <div class="flex justify-between items-center mb-4 pb-4 border-b border-border">
          <span class="text-xs font-mono text-muted-foreground uppercase tracking-wider">Číslo rezervace</span>
          <span class="font-mono font-bold text-foreground">#{{ Math.floor(Math.random() * 10000) + 1000 }}</span>
        </div>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <Calendar class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm text-foreground">{{ calendar.formatDate(startDate) }} — {{ calendar.formatDate(endDate) }}</span>
          </div>
          <div class="flex items-center gap-3">
            <User class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm text-foreground">{{ customer.email }}</span>
          </div>
        </div>
      </div>
      <div class="pt-4">
        <Button as-child variant="outline" class="min-w-[200px]">
          <Link :href="route('welcome')">Zpět na hlavní stránku</Link>
        </Button>
      </div>
    </div>
  </div>
</template>
