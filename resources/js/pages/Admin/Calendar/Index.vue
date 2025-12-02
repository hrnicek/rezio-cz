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
} from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { BookingStatusLabels, BookingStatus } from '@/lib/enums';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';

declare const route: any;

interface MoneyData {
    amount: number;
    value: number;
    currency: string;
    formatted: string;
}

interface CalendarBookingData {
    id: string;
    key: string;
    start: string;
    end: string;
    title: string;
    status: string;
    total_price?: MoneyData;
    guests?: number;
}

interface PropertySimple {
    id: number;
    name: string;
}

const props = defineProps<{
    bookings: CalendarBookingData[];
    properties: PropertySimple[];
}>();

const breadcrumbs = [
    {
        title: 'Kalendář',
        href: '/admin/calendar',
    },
];

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

const onEventClick = (event: any) => {
    const booking = props.bookings.find(b => b.key === event.key);
    if (booking) {
        selectedBooking.value = booking;
        editForm.status = booking.status; 
        editForm.start_date = booking.start;
        editForm.end_date = booking.end;
        editForm.notes = ''; 
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

const calendarAttributes = computed(() => {
    return props.bookings.map(booking => ({
        key: booking.key,
        customData: booking,
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
</script>

<template>
    <Head title="Kalendář" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-4 p-4 h-full flex flex-col">
            <div class="flex items-center justify-between space-y-2">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-foreground">Kalendář obsazenosti</h2>
                    <p class="text-muted-foreground">
                        Přehled všech rezervací a blokovaných termínů.
                    </p>
                </div>
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

            <Card class="flex-1 border-border shadow-none flex flex-col overflow-hidden">
                <CardHeader class="pb-2 flex flex-row items-center justify-between">
                    <CardTitle class="sr-only">Kalendář</CardTitle>
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                            <span class="text-muted-foreground">Potvrzeno</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                            <span class="text-muted-foreground">Čekající</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-muted-foreground">Zrušeno/Jiné</span>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="flex-1 p-0 sm:p-2 h-full">
                    <div class="h-full w-full p-2">
                        <!-- VCalendar configured for full view -->
                        <VCalendar 
                            expanded 
                            borderless
                            :rows="1"
                            :step="1"
                            :attributes="calendarAttributes"
                            class="h-full w-full"
                        >
                            <template #popover="{ attributes }">
                                <div class="px-3 py-2 bg-white dark:bg-slate-800 border border-border rounded-md shadow-lg min-w-[200px] text-left">
                                    <div v-for="attr in attributes" :key="attr.key" class="mb-2 last:mb-0 border-b border-border last:border-0 pb-2 last:pb-0">
                                        <div class="font-semibold text-sm text-foreground">{{ attr.customData.title }}</div>
                                        <div class="text-xs text-muted-foreground mb-1">
                                            {{ new Date(attr.customData.start).toLocaleDateString('cs-CZ') }} - {{ new Date(attr.customData.end).toLocaleDateString('cs-CZ') }}
                                        </div>
                                        <div class="flex items-center justify-between text-xs mt-1">
                                            <span :class="{
                                                'text-green-600': attr.customData.status === 'confirmed',
                                                'text-yellow-600': attr.customData.status === 'pending',
                                                'text-red-600': attr.customData.status === 'cancelled'
                                            }" class="font-medium capitalize">
                                                {{ BookingStatusLabels[attr.customData.status] || attr.customData.status }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-muted-foreground mt-1 flex justify-between items-center">
                                            <span v-if="attr.customData.guests">{{ attr.customData.guests }} os.</span>
                                            <span v-if="attr.customData.total_price" class="font-mono font-medium text-foreground">{{ attr.customData.total_price.formatted }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </VCalendar>
                    </div>
                </CardContent>
            </Card>
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

<style scoped>
/* Custom styling for VCalendar to match the theme */
:deep(.vc-container) {
    --vc-font-family: var(--font-sans);
    --vc-rounded-lg: var(--radius-md);
    --vc-border: 1px solid var(--border);
    border: none;
    background-color: transparent;
}
:deep(.vc-header) {
    padding-bottom: 1rem;
}
:deep(.vc-title) {
    font-weight: 600;
    color: var(--foreground);
    text-transform: capitalize;
}
:deep(.vc-arrow) {
    color: var(--muted-foreground);
}
:deep(.vc-arrow:hover) {
    color: var(--foreground);
    background: var(--accent);
}
:deep(.vc-weekday) {
    color: var(--muted-foreground);
    font-size: 0.875rem;
    font-weight: 500;
    padding-bottom: 0.5rem;
}
:deep(.vc-day) {
    min-height: 100px; /* Make cells taller for better visibility */
    border: 1px solid var(--border);
    margin: -1px 0 0 -1px; /* Collapse borders */
    padding: 4px;
}
:deep(.vc-day-content) {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--foreground);
    margin-bottom: 4px;
}
:deep(.vc-day-content:hover) {
    background-color: var(--accent);
}
/* 
   Ensure highlights are visible. 
   Removed transparency override on vc-highlight to allow default VCalendar highlights.
*/
</style>
