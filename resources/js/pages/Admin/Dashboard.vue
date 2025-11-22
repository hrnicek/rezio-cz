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
import { ref } from 'vue';
import { Textarea } from '@/components/ui/textarea';

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
}>();

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

const onDayClick = (day: any) => {
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
</script>

<template>
    <Head title="Nástěnka" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="flex h-full items-center justify-center p-6">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-foreground">Celkem rezervací</h3>
                            <p class="text-3xl font-bold text-foreground">0</p>
                        </div>
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex h-full items-center justify-center p-6">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-foreground">Tržby</h3>
                            <p class="text-3xl font-bold text-foreground">0,00 Kč</p>
                        </div>
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex h-full items-center justify-center p-6">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-foreground">Čekající</h3>
                            <p class="text-3xl font-bold text-foreground">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Kalendář dostupnosti</h2>
                        <Dialog v-model:open="isBlockDatesOpen">
                            <DialogTrigger as-child>
                                <Button>Blokovat termín</Button>
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
                                                    <SelectItem value="pending">Čekající</SelectItem>
                                                    <SelectItem value="confirmed">Potvrzeno</SelectItem>
                                                    <SelectItem value="cancelled">Zrušeno</SelectItem>
                                                    <SelectItem value="blocked">Blokováno</SelectItem>
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
                    </div>
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>
