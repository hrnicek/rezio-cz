<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ref, computed } from 'vue';
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';
import { BookingStatusLabels } from '@/lib/enums';
import { Calendar as CalendarIcon, CreditCard, Users, Activity, DollarSign } from 'lucide-vue-next';

declare const route: any;

const breadcrumbs = [
    {
        title: 'Nástěnka',
        href: '/admin/dashboard',
    },
];

const props = defineProps<{
    bookings: Array<any>;
    properties: Array<{ id: number; name: string }>;
    stats: {
        total_revenue: number;
        total_bookings: number;
        pending_bookings: number;
    };
}>();

const { formatCurrency } = useCurrency();

const isBlockDatesOpen = ref(false);
const isBookingDetailsOpen = ref(false);
const selectedBooking = ref<any>(null);

const form = useForm({
    property_id: props.properties.length > 0 ? props.properties[0].id.toString() : '',
    start_date: '',
    end_date: '',
});

const editForm = useForm({
    status: '',
    start_date: '',
    end_date: '',
    notes: '',
});

const submitBlockDates = () => {
    form.post(route('admin.bookings.store'), {
        onSuccess: () => {
            isBlockDatesOpen.value = false;
            form.reset('start_date', 'end_date');
        },
    });
};

const onDayClick = (_day: any) => {
    // Optional: Pre-fill block dates modal if clicking on empty day
};

const onEventClick = (event: any) => {
    // Find the booking object from the event attributes
    const booking = props.bookings.find(b => b.key === event.key);
    if (booking) {
        selectedBooking.value = booking;
        editForm.status = booking.customData.status;
        editForm.start_date = booking.dates.start;
        editForm.end_date = booking.dates.end;
        editForm.notes = booking.customData.notes || '';
        isBookingDetailsOpen.value = true;
    }
};

const submitEditBooking = () => {
    if (!selectedBooking.value) return;
    
    editForm.put(route('admin.bookings.update', selectedBooking.value.key), {
        onSuccess: () => {
            isBookingDetailsOpen.value = false;
        },
    });
};

const deleteBooking = () => {
    if (!selectedBooking.value) return;
    
    if (confirm('Opravdu chcete smazat tuto rezervaci?')) {
        useForm({}).delete(route('admin.bookings.destroy', selectedBooking.value.key), {
            onSuccess: () => {
                isBookingDetailsOpen.value = false;
            },
        });
    }
};

// Get recent bookings from the calendar attributes (simplified logic)
const recentBookings = computed(() => {
    return props.bookings
        .filter(b => b.customData && b.customData.status !== 'blocked')
        .slice(0, 5)
        .map(b => ({
            id: b.key,
            name: b.customData?.title || 'Unknown',
            email: 'host@example.com', // Placeholder as we don't have email in attributes
            amount: 0, // Placeholder
            status: b.customData?.status || 'unknown',
            date: new Date(b.dates.start).toLocaleDateString('cs-CZ')
        }));
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <Head title="Nástěnka" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
            <div class="flex items-center justify-between space-y-2">
                <h2 class="text-3xl font-bold tracking-tight">Přehled</h2>
                <div class="flex items-center space-x-2">
                    <Dialog v-model:open="isBlockDatesOpen">
                        <DialogTrigger as-child>
                            <Button>
                                <CalendarIcon class="mr-2 h-4 w-4" />
                                Blokovat termín
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px]">
                            <DialogHeader>
                                <DialogTitle>Blokovat termín</DialogTitle>
                                <DialogDescription>
                                    Vyberte nemovitost a termín pro blokaci.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitBlockDates" class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <Label for="property" class="text-right">Nemovitost</Label>
                                    <Select v-model="form.property_id">
                                        <SelectTrigger class="col-span-3">
                                            <SelectValue placeholder="Vyberte nemovitost" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="property in properties" :key="property.id" :value="property.id.toString()">
                                                {{ property.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <Label for="start_date" class="text-right">Od</Label>
                                    <Input id="start_date" type="date" v-model="form.start_date" class="col-span-3" />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <Label for="end_date" class="text-right">Do</Label>
                                    <Input id="end_date" type="date" v-model="form.end_date" class="col-span-3" />
                                </div>
                                <div v-if="form.errors.start_date" class="text-red-500 text-sm col-span-4 text-right">
                                    {{ form.errors.start_date }}
                                </div>
                            </form>
                            <DialogFooter>
                                <Button type="submit" @click="submitBlockDates" :disabled="form.processing">Blokovat termín</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Celkové tržby</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            +20.1% oproti minulému měsíci
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rezervace</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            +180.1% oproti minulému měsíci
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Čekající</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            Vyžaduje vaši pozornost
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Aktivní nemovitosti</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ properties.length }}</div>
                        <p class="text-xs text-muted-foreground">
                            +2 od začátku roku
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                <Card class="col-span-4">
                    <CardHeader>
                        <CardTitle>Kalendář dostupnosti</CardTitle>
                        <CardDescription>
                            Přehled obsazenosti vašich nemovitostí.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pl-2">
                        <VCalendar expanded :attributes="bookings" @did-move="onDayClick">
                            <template #event="{ event }">
                                <div
                                    class="w-full h-full cursor-pointer"
                                    @click="onEventClick(event)"
                                >
                                    <!-- Render default event content or custom -->
                                </div>
                            </template>
                        </VCalendar>
                    </CardContent>
                </Card>
                <Card class="col-span-3">
                    <CardHeader>
                        <CardTitle>Nedávné rezervace</CardTitle>
                        <CardDescription>
                            Tento měsíc máte {{ stats.total_bookings }} rezervací.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-8">
                            <div v-for="booking in recentBookings" :key="booking.id" class="flex items-center">
                                <Avatar class="h-9 w-9">
                                    <AvatarImage src="/avatars/01.png" alt="Avatar" />
                                    <AvatarFallback>{{ getInitials(booking.name) }}</AvatarFallback>
                                </Avatar>
                                <div class="ml-4 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ booking.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ booking.date }}</p>
                                </div>
                                <div class="ml-auto font-medium">
                                    <span :class="{
                                        'text-yellow-600': booking.status === 'pending',
                                        'text-green-600': booking.status === 'confirmed',
                                        'text-red-600': booking.status === 'cancelled',
                                    }">
                                        {{ BookingStatusLabels[booking.status] || booking.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Booking Details Modal -->
        <Dialog v-model:open="isBookingDetailsOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Detail rezervace</DialogTitle>
                    <DialogDescription>
                        Zobrazit a upravit detaily rezervace.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedBooking" class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label class="text-right font-bold">Host</Label>
                        <div class="col-span-3">{{ selectedBooking.customData.title }}</div>
                    </div>
                    <form @submit.prevent="submitEditBooking" class="grid gap-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_status" class="text-right">Stav</Label>
                            <Select v-model="editForm.status">
                                <SelectTrigger class="col-span-3">
                                    <SelectValue placeholder="Vyberte stav" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in BookingStatusLabels" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_start_date" class="text-right">Od</Label>
                            <Input id="edit_start_date" type="date" v-model="editForm.start_date" class="col-span-3" />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_end_date" class="text-right">Do</Label>
                            <Input id="edit_end_date" type="date" v-model="editForm.end_date" class="col-span-3" />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_notes" class="text-right">Poznámky</Label>
                            <Textarea id="edit_notes" v-model="editForm.notes" class="col-span-3" placeholder="Interní poznámky..." />
                        </div>
                        <div v-if="editForm.errors.start_date" class="text-red-500 text-sm col-span-4 text-right">
                            {{ editForm.errors.start_date }}
                        </div>
                    </form>
                </div>
                <DialogFooter class="flex justify-between sm:justify-between">
                    <Button variant="destructive" @click="deleteBooking">Smazat</Button>
                    <Button type="submit" @click="submitEditBooking" :disabled="editForm.processing">Uložit změny</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
