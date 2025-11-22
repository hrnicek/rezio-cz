<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Calendar, User, Home, CreditCard } from 'lucide-vue-next';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';

declare const route: any;

const props = defineProps<{
  booking: {
    id: number;
    code: string;
    property: { id: number; name: string; address: string };
    customer: { 
      id: number; 
      first_name: string; 
      last_name: string; 
      email: string; 
      phone: string;
      note?: string;
    } | null;
    start_date: string;
    end_date: string;
    total_price: number;
    status: string;
    notes?: string;
    created_at: string;
    updated_at: string;
    payments: Array<{
      id: number;
      amount: number;
      payment_method: string;
      paid_at: string;
      status: string;
    }>;
  };
}>();

const { formatCurrency } = useCurrency();
const selectedStatus = ref(props.booking.status);

const updateStatus = () => {
  router.put(route('admin.bookings.update', props.booking.id), {
    status: selectedStatus.value
  });
};

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'confirmed': return 'default';
    case 'pending': return 'secondary';
    case 'cancelled': return 'destructive';
    case 'paid': return 'outline';
    default: return 'secondary';
  }
};

const breadcrumbs = [
  { title: 'Rezervace', href: route('admin.bookings.index') },
  { title: `#${props.booking.code}`, href: route('admin.bookings.show', props.booking.id) },
];
</script>

<template>
  <Head :title="`Rezervace #${booking.code}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button variant="outline" size="icon" as-child>
            <Link :href="route('admin.bookings.index')">
              <ArrowLeft class="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h2 class="text-2xl font-bold tracking-tight">Rezervace #{{ booking.code }}</h2>
            <p class="text-sm text-muted-foreground">
              Vytvořeno {{ new Date(booking.created_at).toLocaleDateString('cs-CZ') }}
            </p>
          </div>
        </div>
        <Badge :variant="getStatusVariant(booking.status)">
          {{ booking.status }}
        </Badge>
      </div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <!-- Informace o Rezervaci -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Calendar class="h-4 w-4" />
              Informace o Rezervaci
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground">Kód rezervace</div>
              <div class="text-lg font-semibold">{{ booking.code }}</div>
            </div>
            <Separator />
            <div>
              <div class="text-sm font-medium text-muted-foreground">Příjezd</div>
              <div>{{ new Date(booking.start_date).toLocaleDateString('cs-CZ') }}</div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground">Odjezd</div>
              <div>{{ new Date(booking.end_date).toLocaleDateString('cs-CZ') }}</div>
            </div>
            <Separator />
            <div>
              <div class="text-sm font-medium text-muted-foreground">Celková cena</div>
              <div class="text-2xl font-bold text-primary">{{ formatCurrency(booking.total_price) }}</div>
            </div>
            <Separator />
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-2">Stav</div>
              <Select v-model="selectedStatus" @update:model-value="updateStatus">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pending">Čekající</SelectItem>
                  <SelectItem value="confirmed">Potvrzeno</SelectItem>
                  <SelectItem value="cancelled">Zrušeno</SelectItem>
                  <SelectItem value="paid">Zaplaceno</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div v-if="booking.notes">
              <div class="text-sm font-medium text-muted-foreground">Poznámky</div>
              <div class="text-sm text-gray-600">{{ booking.notes }}</div>
            </div>
          </CardContent>
        </Card>

        <!-- Informace o Nemovitosti -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Home class="h-4 w-4" />
              Nemovitost
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground">Název</div>
              <div class="font-semibold">{{ booking.property.name }}</div>
            </div>
            <Separator />
            <div>
              <div class="text-sm font-medium text-muted-foreground">Adresa</div>
              <div class="text-sm">{{ booking.property.address }}</div>
            </div>
            <Separator />
            <Button variant="outline" size="sm" as-child class="w-full">
              <Link :href="route('admin.properties.edit', booking.property.id)">
                Zobrazit nemovitost
              </Link>
            </Button>
          </CardContent>
        </Card>

        <!-- Informace o Zákazníkovi -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-4 w-4" />
              Zákazník
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-if="booking.customer">
              <div>
                <div class="text-sm font-medium text-muted-foreground">Jméno</div>
                <div class="font-semibold">
                  {{ booking.customer.first_name }} {{ booking.customer.last_name }}
                </div>
              </div>
              <Separator class="my-4" />
              <div>
                <div class="text-sm font-medium text-muted-foreground">Email</div>
                <div class="text-sm">
                  <a :href="`mailto:${booking.customer.email}`" class="text-primary hover:underline">
                    {{ booking.customer.email }}
                  </a>
                </div>
              </div>
              <Separator class="my-4" />
              <div>
                <div class="text-sm font-medium text-muted-foreground">Telefon</div>
                <div class="text-sm">
                  <a :href="`tel:${booking.customer.phone}`" class="text-primary hover:underline">
                    {{ booking.customer.phone }}
                  </a>
                </div>
              </div>
              <div v-if="booking.customer.note" class="mt-4">
                <div class="text-sm font-medium text-muted-foreground">Poznámka zákazníka</div>
                <div class="text-sm text-gray-600 italic">"{{ booking.customer.note }}"</div>
              </div>
            </div>
            <div v-else class="text-sm text-muted-foreground italic">
              Žádné informace o zákazníkovi
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Platby -->
      <Card v-if="booking.payments.length > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <CreditCard class="h-4 w-4" />
            Platby
          </CardTitle>
          <CardDescription>Historie plateb pro tuto rezervaci</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="payment in booking.payments" 
              :key="payment.id"
              class="flex items-center justify-between rounded-lg border p-4"
            >
              <div>
                <div class="font-medium">{{ formatCurrency(payment.amount) }}</div>
                <div class="text-sm text-muted-foreground">
                  {{ payment.payment_method }} • {{ new Date(payment.paid_at).toLocaleDateString('cs-CZ') }}
                </div>
              </div>
              <Badge :variant="payment.status === 'completed' ? 'default' : 'secondary'">
                {{ payment.status }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
