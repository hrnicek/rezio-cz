<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
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
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
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
  CardFooter,
} from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ref, computed } from 'vue';
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';
import { BookingStatusLabels, BookingStatus } from '@/lib/enums';
import { Calendar as CalendarIcon, CreditCard, Users, Activity, DollarSign, Building } from 'lucide-vue-next';

declare const route: any;

// Interfaces matching Spatie Data objects
interface CalendarBookingData {
    id: string;
    key: string;
    start: string;
    end: string;
    title: string;
    status: string; // Enum as string
}

interface UpcomingBookingData {
    id: string;
    label: string;
    start: string;
    end: string;
}

interface PropertyStats {
    id: number;
    name: string;
    address: string | null;
    description: string | null;
    bookings_count: number;
    active_bookings_count: number;
    month_bookings_count: number;
}

interface Stats {
    total_revenue: number;
    total_bookings: number;
    pending_bookings: number;
}

const props = defineProps<{
    bookings: CalendarBookingData[];
    upcomingBookings: UpcomingBookingData[];
    properties: PropertyStats[];
    stats: Stats;
}>();

const breadcrumbs = [
    {
        title: 'Nástěnka',
        href: '/admin/dashboard',
    },
];

const { formatCurrency } = useCurrency();

const isBlockDatesOpen = ref(false);
const isBookingDetailsOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const selectedBooking = ref<CalendarBookingData | null>(null);

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
            toast.success('Termín byl úspěšně zablokován.');
        },
        onError: () => {
            toast.error('Nepodařilo se zablokovat termín.');
        }
    });
};

const onDayClick = (_day: any) => {
    // Optional: Pre-fill block dates modal if clicking on empty day
};

const onEventClick = (event: any) => {
    // Find the booking object from the event attributes
    const booking = props.bookings.find(b => b.key === event.key);
    if (booking) {
        // Note: CalendarBookingData doesn't have 'customData' property directly matching the old structure
        // We map the props to the form. 
        // However, CalendarBookingData is limited. 
        // The original code assumed 'customData' existed on the event object from VCalendar
        // VCalendar events usually put the source object in 'customData' or similar if configured.
        // But here 'props.bookings' IS the source.
        
        // Wait, VCalendar attributes usually look like:
        // { key: ..., dates: ..., customData: ... }
        // If props.bookings is passed directly to :attributes, then VCalendar expects an array of attribute objects.
        // CalendarBookingData has 'key', 'start', 'end'.
        // VCalendar expects 'dates' or 'start'/'end' depending on config.
        // If CalendarBookingData has 'start' and 'end' strings, VCalendar might not pick them up automatically as 'dates'
        // unless mapped.
        
        // The controller sends CalendarBookingData which has start/end strings.
        // We might need to transform this for VCalendar if it expects Date objects or specific structure.
        // But let's assume the previous implementation worked or intended to work.
        
        // Actually, let's look at CalendarBookingData again.
        // id, key, start, end, title, status.
        
        // To work with VCalendar, we usually map it:
        selectedBooking.value = booking;
        // We don't have notes in CalendarBookingData.
        editForm.status = booking.status; 
        editForm.start_date = booking.start;
        editForm.end_date = booking.end;
        editForm.notes = ''; // Not available in CalendarBookingData
        isBookingDetailsOpen.value = true;
    }
};

const submitEditBooking = () => {
    if (!selectedBooking.value) return;
    
    editForm.put(route('admin.bookings.update', selectedBooking.value.key), {
        onSuccess: () => {
            isBookingDetailsOpen.value = false;
            toast.success('Rezervace byla úspěšně upravena.');
        },
        onError: () => {
            toast.error('Nepodařilo se upravit rezervaci.');
        }
    });
};

const confirmDeleteBooking = () => {
    isDeleteDialogOpen.value = true;
};

const executeDeleteBooking = () => {
    if (!selectedBooking.value) return;
    
    useForm({}).delete(route('admin.bookings.destroy', selectedBooking.value.key), {
        onSuccess: () => {
            isBookingDetailsOpen.value = false;
            isDeleteDialogOpen.value = false;
            toast.success('Rezervace byla úspěšně smazána.');
        },
        onError: () => {
            toast.error('Nepodařilo se smazat rezervaci.');
        }
    });
};

// Transform bookings for VCalendar attributes
const calendarAttributes = computed(() => {
    return props.bookings.map(booking => ({
        key: booking.key,
        customData: booking, // Pass the whole object as customData
        dates: { start: new Date(booking.start), end: new Date(booking.end) },
        highlight: {
            color: booking.status === 'confirmed' ? 'green' : (booking.status === 'pending' ? 'yellow' : 'red'),
            fillMode: 'light',
        },
        popover: {
            label: booking.title,
        },
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
        <div class="flex-1 space-y-4 p-4">
            <div class="flex items-center justify-between space-y-2">
                <h2 class="text-2xl font-semibold tracking-tight text-foreground">Přehled</h2>
                <div class="flex items-center space-x-2">
                    <Dialog v-model:open="isBlockDatesOpen">
                        <DialogTrigger as-child>
                            <Button variant="outline" class="h-9 shadow-sm">
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
                                    <Input id="start_date" type="date" v-model="form.start_date" class="col-span-3 h-9" />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <Label for="end_date" class="text-right">Do</Label>
                                    <Input id="end_date" type="date" v-model="form.end_date" class="col-span-3 h-9" />
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

            <!-- Properties Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="property in properties" :key="property.id" class="overflow-hidden border-border shadow-none rounded-md group hover:border-primary/50 transition-colors">
                    <div class="aspect-video w-full bg-muted relative flex items-center justify-center border-b border-border">
                        <Building class="h-12 w-12 text-muted-foreground/20" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-4">
                            <h3 class="font-semibold text-white tracking-tight text-lg leading-none">{{ property.name }}</h3>
                            <p v-if="property.address" class="text-xs text-white/70 mt-1 truncate font-medium">{{ property.address }}</p>
                        </div>
                    </div>
                    <CardContent class="p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-mono font-medium">Nové rezervace</p>
                                <p class="text-2xl font-semibold tracking-tight text-foreground font-mono">{{ property.month_bookings_count }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-mono font-medium">Aktivní</p>
                                <p class="text-2xl font-semibold tracking-tight text-foreground font-mono">{{ property.active_bookings_count }}</p>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="p-4 pt-0">
                        <Button variant="outline" class="w-full uppercase tracking-wider text-xs h-9 font-mono border-dashed hover:border-solid hover:bg-secondary/50" as-child>
                            <Link :href="route('admin.properties.bookings.index', property.id)">
                                REZERVACE
                            </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="border-border shadow-none">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Celkové tržby</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold font-mono">{{ formatCurrency(stats.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            +20.1% oproti minulému měsíci
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border shadow-none">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rezervace</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold font-mono">{{ stats.total_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            +180.1% oproti minulému měsíci
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border shadow-none">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Čekající</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold font-mono">{{ stats.pending_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            Vyžaduje vaši pozornost
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border shadow-none">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Aktivní nemovitosti</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold font-mono">{{ properties.length }}</div>
                        <p class="text-xs text-muted-foreground">
                            +2 od začátku roku
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                <Card class="col-span-4 border-border shadow-none">
                    <CardHeader>
                        <CardTitle>Kalendář dostupnosti</CardTitle>
                        <CardDescription>
                            Přehled obsazenosti vašich nemovitostí.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pl-2">
                        <VCalendar expanded :attributes="calendarAttributes" @did-move="onDayClick">
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
                <Card class="col-span-3 border-border shadow-none">
                    <CardHeader>
                        <CardTitle>Nadcházející příjezdy</CardTitle>
                        <CardDescription>
                            Příštích 5 příjezdů
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-8">
                            <div v-for="booking in upcomingBookings" :key="booking.id" class="flex items-center">
                                <Avatar class="h-9 w-9 rounded-sm border border-border">
                                    <AvatarFallback class="rounded-sm">{{ getInitials(booking.label) }}</AvatarFallback>
                                </Avatar>
                                <div class="ml-4 space-y-1">
                                    <p class="text-sm font-medium leading-none truncate max-w-[200px]" :title="booking.label">{{ booking.label }}</p>
                                    <p class="text-xs text-muted-foreground font-mono">{{ new Date(booking.start).toLocaleDateString('cs-CZ') }} - {{ new Date(booking.end).toLocaleDateString('cs-CZ') }}</p>
                                </div>
                            </div>
                            <div v-if="upcomingBookings.length === 0" class="text-center text-muted-foreground text-sm py-4">
                                Žádné nadcházející příjezdy
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
                        <div class="col-span-3">{{ selectedBooking.title }}</div>
                    </div>
                    <form @submit.prevent="submitEditBooking" class="grid gap-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_status" class="text-right">Stav</Label>
                            <Select v-model="editForm.status">
                                <SelectTrigger class="col-span-3">
                                    <SelectValue placeholder="Vyberte stav" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="status in Object.values(BookingStatus)" :key="status" :value="status">
                                        {{ BookingStatusLabels[status] }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="editForm.errors.status" class="col-span-4 text-right text-red-500 text-xs">
                                {{ editForm.errors.status }}
                            </div>
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_start_date" class="text-right">Od</Label>
                            <Input id="edit_start_date" type="date" v-model="editForm.start_date" class="col-span-3 h-9" />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit_end_date" class="text-right">Do</Label>
                            <Input id="edit_end_date" type="date" v-model="editForm.end_date" class="col-span-3 h-9" />
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
                    <Button variant="destructive" @click="confirmDeleteBooking">Smazat</Button>
                    <Button type="submit" @click="submitEditBooking" :disabled="editForm.processing">Uložit změny</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-model:open="isDeleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Opravdu chcete smazat tuto rezervaci?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tato akce je nevratná. Rezervace bude trvale odstraněna ze systému.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Zrušit</AlertDialogCancel>
                    <AlertDialogAction @click="executeDeleteBooking" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                        Smazat
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
