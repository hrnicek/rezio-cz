<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
    ArrowLeft, Calendar, User, Mail, Phone, MapPin, 
    ExternalLink, Copy, CheckCircle2, XCircle, MoreVertical,
    Pencil, Trash2, Send, Download
} from 'lucide-vue-next';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Progress } from '@/components/ui/progress';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { BookingStatusLabels, PaymentStatus } from '@/lib/enums';

declare const route: any;

const props = defineProps<{
    booking: any;
}>();

const isEditOpen = ref(false);

const form = useForm({
    status: props.booking.status,
    start_date: props.booking.start_date,
    end_date: props.booking.end_date,
    notes: props.booking.notes || '',
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('cs-CZ', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('cs-CZ', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('cs-CZ', {
        style: 'currency',
        currency: 'CZK',
        maximumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'default'; // green-ish in default theme usually, or primary
        case 'pending': return 'secondary';
        case 'cancelled': return 'destructive';
        case 'paid': return 'outline';
        default: return 'secondary';
    }
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    // Ideal place for a toast
};

const calculateNights = (start: string, end: string) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const paidAmount = computed(() => {
    return props.booking.payments.reduce((sum: number, payment: any) => {
        return payment.status === PaymentStatus.Paid ? sum + Number(payment.amount) : sum;
    }, 0);
});

const paymentProgress = computed(() => {
    if (props.booking.total_price === 0) return 0;
    return Math.min(100, (paidAmount.value / props.booking.total_price) * 100);
});

const getInitials = (firstName: string, lastName: string) => {
    return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase();
};

const updateStatus = (status: string) => {
    if (confirm(`Opravdu chcete změnit stav na "${BookingStatusLabels[status] || status}"?`)) {
        router.put(route('admin.bookings.update', props.booking.id), { status });
    }
};

const deleteBooking = () => {
    if (confirm('Opravdu chcete smazat tuto rezervaci? Tato akce je nevratná.')) {
        router.delete(route('admin.bookings.destroy', props.booking.id));
    }
};

const submitEdit = () => {
    form.put(route('admin.bookings.update', props.booking.id), {
        onSuccess: () => {
            isEditOpen.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Rezervace #${props.booking.code}`" />

        <div class="flex flex-col gap-6 p-4 md:p-8 mx-auto w-full">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('admin.bookings.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">Rezervace #{{ booking.code }}</h1>
                            <Badge :variant="getStatusColor(booking.status)">
                                {{ BookingStatusLabels[booking.status] || booking.status }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">
                            Vytvořeno {{ formatDateTime(booking.created_at) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="isEditOpen = true">
                        <Pencil class="mr-2 h-4 w-4" /> Upravit
                    </Button>
                    
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Akce</DropdownMenuLabel>
                            <DropdownMenuItem @click="updateStatus('confirmed')" v-if="booking.status === 'pending'">
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Potvrdit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="updateStatus('cancelled')" v-if="booking.status !== 'cancelled'">
                                <XCircle class="mr-2 h-4 w-4" /> Zrušit
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-destructive" @click="deleteBooking">
                                <Trash2 class="mr-2 h-4 w-4" /> Smazat
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column (Details) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Trip Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Detaily pobytu</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-6">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Nemovitost</p>
                                    <div class="flex items-center gap-2 font-medium text-lg">
                                        <MapPin class="h-5 w-5 text-primary" />
                                        {{ booking.property.name }}
                                    </div>
                                </div>
                                <div class="text-right space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Délka pobytu</p>
                                    <p class="font-medium">{{ calculateNights(booking.start_date, booking.end_date) }} nocí</p>
                                </div>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Příjezd</p>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(booking.start_date) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground pl-6">od 15:00</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Odjezd</p>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(booking.end_date) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground pl-6">do 10:00</p>
                                </div>
                            </div>
                            
                            <div v-if="booking.notes" class="rounded-md bg-muted p-4 mt-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-medium">Poznámka</span>
                                </div>
                                <p class="text-sm text-muted-foreground whitespace-pre-line">{{ booking.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tabs for Guests, Check-in, Payments (Detailed) -->
                    <Tabs defaultValue="guests" class="w-full">
                        <TabsList class="grid w-full grid-cols-3">
                            <TabsTrigger value="guests">Hosté</TabsTrigger>
                            <TabsTrigger value="checkin">Online Check-in</TabsTrigger>
                            <TabsTrigger value="payments">Platby</TabsTrigger>
                        </TabsList>
                        
                        <TabsContent value="guests" class="mt-4">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between">
                                    <CardTitle>Seznam hostů</CardTitle>
                                    <Badge variant="secondary">{{ booking.guests.length }}</Badge>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="booking.guests.length > 0" class="space-y-4">
                                        <div v-for="guest in booking.guests" :key="guest.id" class="flex items-center justify-between p-3 border rounded-md bg-card hover:bg-accent/50 transition-colors">
                                            <div class="flex items-center gap-4">
                                                <Avatar class="h-9 w-9 rounded-md">
                                                    <AvatarFallback class="rounded-md">{{ getInitials(guest.first_name, guest.last_name) }}</AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <p class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</p>
                                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                                        <span>{{ guest.is_adult ? 'Dospělý' : 'Dítě' }}</span>
                                                        <span v-if="guest.nationality">• {{ guest.nationality }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right text-sm text-muted-foreground">
                                                <div v-if="guest.document_number">
                                                    {{ guest.document_type === 'passport' ? 'Pas' : 'OP' }}: {{ guest.document_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 text-muted-foreground border-2 border-dashed rounded-md">
                                        <User class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                        <p>Zatím nebyli přidáni žádní hosté.</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        
                        <TabsContent value="checkin" class="mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Online Check-in</CardTitle>
                                    <CardDescription>Odkaz pro hosty k vyplnění údajů</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div v-if="booking.checkin_token" class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <Input readonly :value="route('check-in.show', booking.checkin_token)" />
                                        </div>
                                        <Button variant="outline" size="icon" @click="copyToClipboard(route('check-in.show', booking.checkin_token))">
                                            <Copy class="h-4 w-4" />
                                        </Button>
                                        <Button variant="outline" size="icon" as-child>
                                            <a :href="route('check-in.show', booking.checkin_token)" target="_blank">
                                                <ExternalLink class="h-4 w-4" />
                                            </a>
                                        </Button>
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        <p>Tento odkaz můžete zaslat hostům pro předvyplnění údajů online check-inu.</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="payments" class="mt-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Historie plateb</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Částka</TableHead>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Datum</TableHead>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Stav</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="payment in booking.payments" :key="payment.id">
                                                <TableCell class="font-medium">{{ formatCurrency(payment.amount) }}</TableCell>
                                                <TableCell>
                                                    <div>Splatnost: {{ formatDate(payment.due_date) }}</div>
                                                    <div v-if="payment.paid_at" class="text-xs text-muted-foreground">
                                                        Zaplaceno: {{ formatDate(payment.paid_at) }}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge :variant="payment.status === 'paid' ? 'outline' : 'secondary'">
                                                        {{ payment.status === 'paid' ? 'Zaplaceno' : 'Čeká na platbu' }}
                                                    </Badge>
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-if="booking.payments.length === 0">
                                                <TableCell colspan="3" class="text-center text-muted-foreground py-6">
                                                    Žádné záznamy o platbách
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>

                <!-- Right Column (Customer & Summary) -->
                <div class="space-y-6">
                    <!-- Customer Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Host</CardTitle>
                        </CardHeader>
                        <CardContent v-if="booking.customer" class="space-y-6">
                            <div class="flex items-center gap-4">
                                <Avatar class="h-9 w-9">
                                    <AvatarFallback class="bg-primary/10 text-primary">
                                        {{ getInitials(booking.customer.first_name, booking.customer.last_name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="font-medium text-lg">{{ booking.customer.first_name }} {{ booking.customer.last_name }}</p>
                                    <p class="text-sm text-muted-foreground">Hlavní host</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <a :href="'mailto:' + booking.customer.email" class="hover:underline hover:text-primary truncate">
                                        {{ booking.customer.email }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <a :href="'tel:' + booking.customer.phone" class="hover:underline hover:text-primary">
                                        {{ booking.customer.phone }}
                                    </a>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <Button class="flex-1" variant="outline" as-child>
                                    <a :href="'mailto:' + booking.customer.email">
                                        <Send class="mr-2 h-4 w-4" /> Email
                                    </a>
                                </Button>
                                <Button class="flex-1" variant="outline" as-child>
                                    <a :href="'tel:' + booking.customer.phone">
                                        <Phone class="mr-2 h-4 w-4" /> Volat
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                        <CardContent v-else>
                            <p class="text-sm text-muted-foreground">Informace o hostovi nejsou k dispozici.</p>
                        </CardContent>
                    </Card>

                    <!-- Payment Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Platba</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Celkem k úhradě</span>
                                    <span class="font-bold">{{ formatCurrency(booking.total_price) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Zaplaceno</span>
                                    <span class="text-green-600 font-medium">{{ formatCurrency(paidAmount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Zbývá doplatit</span>
                                    <span class="text-destructive font-medium">{{ formatCurrency(booking.total_price - paidAmount) }}</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <Progress :model-value="paymentProgress" class="h-2" />
                                <div class="flex justify-between text-xs text-muted-foreground">
                                    <span>{{ Math.round(paymentProgress) }}% uhrazeno</span>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="bg-muted/50 p-4">
                            <Button class="w-full" variant="secondary">
                                <Download class="mr-2 h-4 w-4" /> Faktura (PDF)
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Edit Dialog -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Upravit rezervaci</DialogTitle>
                    <DialogDescription>
                        Změny v termínu mohou ovlivnit dostupnost a cenu.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="status" class="text-right">Stav</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="col-span-3">
                                <SelectValue placeholder="Vyberte stav" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Čekající</SelectItem>
                                <SelectItem value="confirmed">Potvrzeno</SelectItem>
                                <SelectItem value="cancelled">Zrušeno</SelectItem>
                                <SelectItem value="paid">Zaplaceno</SelectItem>
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
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="notes" class="text-right">Poznámky</Label>
                        <Textarea id="notes" v-model="form.notes" class="col-span-3" />
                    </div>
                </form>
                <DialogFooter>
                    <Button type="submit" @click="submitEdit" :disabled="form.processing">Uložit změny</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
