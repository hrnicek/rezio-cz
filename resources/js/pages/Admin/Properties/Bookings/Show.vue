<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
    ArrowLeft, Calendar, User, Mail, Phone, MapPin, 
    ExternalLink, Copy, CheckCircle2, XCircle, MoreVertical,
    Pencil, Trash2, Building2, FileText, Plus
} from 'lucide-vue-next';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { BookingStatusLabels } from '@/lib/enums';
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

declare const route: any;

// Interfaces based on Spatie Data classes
interface PropertyData {
    id: number;
    name: string;
    address: string | null;
    image: string | null;
    description: string | null;
}

interface CustomerData {
    id: string;
    email: string;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    phone?: string | null;
    is_company: boolean;
    company_name?: string | null;
    ico?: string | null;
    dic?: string | null;
    billing_street?: string | null;
    billing_city?: string | null;
    billing_zip?: string | null;
    billing_country?: string | null;
    internal_notes?: string | null;
}

interface MoneyData {
    amount: number; // Cents
    value: number; // Float
    currency: string;
    formatted: string;
}

interface PaymentData {
    id: string;
    amount: MoneyData;
    payment_method: string; // Enum value
    paid_at: string | null;
    status: string; // Enum value
}

interface GuestData {
    id: string;
    first_name: string;
    last_name: string;
    is_adult: boolean;
    nationality?: string | null;
    document_type?: string | null;
    document_number?: string | null;
}

interface BookingData {
    id: string;
    code: string | null;
    property: PropertyData;
    customer: CustomerData | null;
    check_in_date: string; // Y-m-d
    check_out_date: string; // Y-m-d
    total_price: MoneyData;
    status: string;
    notes: string | null;
    created_at: string;
    updated_at: string;
    payments: PaymentData[];
    token: string | null;
    guests: GuestData[];
    arrival_time_estimate?: string | null;
    departure_time_estimate?: string | null;
    checked_in_at?: string | null;
    checked_out_at?: string | null;
}

const props = defineProps<{
    booking: BookingData;
}>();

const isPaymentDialogOpen = ref(false);
const paymentForm = useForm({
    amount: '',
    paid_at: new Date().toISOString().split('T')[0],
    notes: '',
});

const submitPayment = () => {
    paymentForm.post(route('admin.bookings.payments.store', props.booking.id), {
        onSuccess: () => {
            isPaymentDialogOpen.value = false;
            paymentForm.reset();
            paymentForm.paid_at = new Date().toISOString().split('T')[0];
            toast.success('Platba byla úspěšně přidána.');
        },
        onError: () => {
            toast.error('Nepodařilo se přidat platbu.');
        }
    });
};

const paidAmount = computed(() => {
    return props.booking.payments.reduce((sum: number, payment: PaymentData) => {
        // Assuming 'paid' status means strictly paid. 
        // Adjust comparison if status is 'succeeded' or similar depending on Enum serialization
        return (payment.status === 'paid' || payment.status === 'succeeded') ? sum + payment.amount.amount : sum;
    }, 0);
});

const remainingToPay = computed(() => {
    return props.booking.total_price.amount - paidAmount.value;
});

// Helper to parse amount for calculation
const currentPaymentAmount = computed(() => {
    return Number(paymentForm.amount) * 100; // Convert to cents
});

const remainingAfterPayment = computed(() => {
    return remainingToPay.value - currentPaymentAmount.value;
});

const deletePayment = (paymentId: string) => {
    if (confirm('Opravdu smazat tuto platbu?')) {
        router.delete(route('admin.bookings.payments.destroy', [props.booking.id, paymentId]), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Platba byla smazána.');
            }
        });
    }
};

const isEditOpen = ref(false);
const bookingStatusToUpdate = ref<string | null>(null);
const isUpdateDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);

const form = useForm({
    status: props.booking.status,
    check_in_date: props.booking.check_in_date,
    check_out_date: props.booking.check_out_date,
    notes: props.booking.notes || '',
});

const formatDate = (date: string | null | undefined) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('cs-CZ', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatDateTime = (date: string | null | undefined) => {
    if (!date) return '';
    return new Date(date).toLocaleString('cs-CZ', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amountCents: number) => {
    return new Intl.NumberFormat('cs-CZ', {
        style: 'currency',
        currency: 'CZK',
        maximumFractionDigits: 0,
    }).format(amountCents / 100);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'outline';
        case 'checked_in': return 'default';
        case 'checked_out': return 'secondary';
        case 'pending': return 'secondary';
        case 'cancelled': return 'destructive';
        case 'no_show': return 'destructive';
        default: return 'secondary';
    }
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.success('Zkopírováno do schránky.');
};

const calculateNights = (start: string, end: string) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getInitials = (firstName?: string | null, lastName?: string | null) => {
    return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase();
};

const confirmUpdateStatus = (status: string) => {
    bookingStatusToUpdate.value = status;
    isUpdateDialogOpen.value = true;
};

const executeUpdateStatus = () => {
    if (!bookingStatusToUpdate.value) return;
    
    router.put(route('admin.bookings.update', props.booking.id), { status: bookingStatusToUpdate.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Stav rezervace byl úspěšně změněn.');
            isUpdateDialogOpen.value = false;
            bookingStatusToUpdate.value = null;
        },
        onError: () => {
             toast.error('Nepodařilo se změnit stav rezervace.');
        }
    });
};

const confirmDeleteBooking = () => {
    isDeleteDialogOpen.value = true;
};

const executeDeleteBooking = () => {
    router.delete(route('admin.bookings.destroy', props.booking.id), {
        onSuccess: () => {
            toast.success('Rezervace byla úspěšně smazána.');
            isDeleteDialogOpen.value = false;
        },
         onError: () => {
             toast.error('Nepodařilo se smazat rezervaci.');
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Rezervace #${props.booking.code}`" />

        <div class="flex flex-col gap-6 p-4 md:p-8 mx-auto w-full max-w-7xl">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-9 w-9">
                        <Link :href="route('admin.properties.bookings.index', booking.property.id)">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">Rezervace #{{ booking.code }}</h1>
                            <Badge :variant="getStatusColor(booking.status)" class="rounded-md px-2.5 py-0.5 font-normal">
                                {{ BookingStatusLabels[booking.status] || booking.status }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1 font-mono">
                            Vytvořeno {{ formatDateTime(booking.created_at) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Edit functionality would go here (e.g. Dialog) -->
                    
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon" class="h-9 w-9">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Akce</DropdownMenuLabel>
                            <DropdownMenuItem @click="confirmUpdateStatus('confirmed')" v-if="['pending', 'no_show', 'checked_in'].includes(booking.status)">
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Potvrdit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmUpdateStatus('checked_in')" v-if="['confirmed', 'checked_out'].includes(booking.status)">
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Ubytovat
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmUpdateStatus('checked_out')" v-if="booking.status === 'checked_in'">
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Odhlásit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmUpdateStatus('no_show')" v-if="booking.status === 'confirmed'">
                                <XCircle class="mr-2 h-4 w-4" /> Nedostavil se
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmUpdateStatus('pending')" v-if="['cancelled', 'no_show'].includes(booking.status)">
                                <CheckCircle2 class="mr-2 h-4 w-4" /> Znovu otevřít
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="confirmUpdateStatus('cancelled')" v-if="['pending', 'confirmed'].includes(booking.status)">
                                <XCircle class="mr-2 h-4 w-4" /> Zrušit
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-destructive" @click="confirmDeleteBooking">
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
                    <Card class="shadow-none border-border">
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
                                    <p class="font-medium">{{ calculateNights(booking.check_in_date, booking.check_out_date) }} nocí</p>
                                </div>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Příjezd</p>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(booking.check_in_date) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground pl-6">od 15:00</p>
                                    <p v-if="booking.checked_in_at" class="text-xs text-green-600 font-medium pl-6 mt-1">
                                        Check-in: {{ formatDateTime(booking.checked_in_at) }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">Odjezd</p>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(booking.check_out_date) }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground pl-6">do 10:00</p>
                                    <p v-if="booking.checked_out_at" class="text-xs text-green-600 font-medium pl-6 mt-1">
                                        Check-out: {{ formatDateTime(booking.checked_out_at) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="booking.notes" class="rounded-md bg-muted/50 border border-border p-4 mt-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-medium">Poznámka</span>
                                </div>
                                <p class="text-sm text-muted-foreground whitespace-pre-line">{{ booking.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tabs for Guests, Check-in, Payments (Detailed) -->
                    <Tabs defaultValue="guests" class="w-full">
                        <TabsList class="grid w-full grid-cols-3 h-10">
                            <TabsTrigger value="guests">Hosté</TabsTrigger>
                            <TabsTrigger value="checkin">Online Check-in</TabsTrigger>
                            <TabsTrigger value="payments">Platby</TabsTrigger>
                        </TabsList>
                        
                        <TabsContent value="guests" class="mt-4">
                            <Card class="shadow-none border-border">
                                <CardHeader class="flex flex-row items-center justify-between">
                                    <CardTitle>Seznam hostů</CardTitle>
                                    <Badge variant="secondary" class="font-mono">{{ booking.guests.length }}</Badge>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="booking.guests.length > 0" class="space-y-4">
                                        <div v-for="guest in booking.guests" :key="guest.id" class="flex items-center justify-between p-3 border rounded-md bg-card hover:bg-accent/50 transition-colors">
                                            <div class="flex items-center gap-4">
                                                <Avatar class="h-9 w-9 rounded-md">
                                                    <AvatarFallback class="rounded-md text-xs">{{ getInitials(guest.first_name, guest.last_name) }}</AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <p class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</p>
                                                    <div class="flex items-center gap-2 text-xs text-muted-foreground font-mono">
                                                        <span>{{ guest.is_adult ? 'DOSPĚLÝ' : 'DÍTĚ' }}</span>
                                                        <span v-if="guest.nationality">• {{ guest.nationality }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right text-sm text-muted-foreground">
                                                <div v-if="guest.document_number" class="font-mono text-xs">
                                                    {{ guest.document_type === 'passport' ? 'PAS' : 'OP' }}: {{ guest.document_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-12 text-muted-foreground border-2 border-dashed rounded-md">
                                        <User class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                        <p>Zatím nebyli přidáni žádní hosté.</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        
                        <TabsContent value="checkin" class="mt-4">
                            <Card class="shadow-none border-border">
                                <CardHeader>
                                    <CardTitle>Online Check-in</CardTitle>
                                    <CardDescription>Odkaz pro hosty k vyplnění údajů</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div v-if="booking.token" class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <Input readonly :value="route('check-in.show', booking.token)" class="font-mono text-xs h-9" />
                                        </div>
                                        <Button variant="outline" size="icon" class="h-9 w-9" @click="copyToClipboard(route('check-in.show', booking.token))">
                                            <Copy class="h-4 w-4" />
                                        </Button>
                                        <Button variant="outline" size="icon" class="h-9 w-9" as-child>
                                            <a :href="route('check-in.show', booking.token)" target="_blank">
                                                <ExternalLink class="h-4 w-4" />
                                            </a>
                                        </Button>
                                    </div>
                                    <div v-else class="text-sm text-muted-foreground">
                                        <p>Check-in token není k dispozici.</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-border">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Odhadovaný příjezd</p>
                                            <p class="font-mono">{{ booking.arrival_time_estimate || 'Nezadáno' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Odhadovaný odjezd</p>
                                            <p class="font-mono">{{ booking.departure_time_estimate || 'Nezadáno' }}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="payments" class="mt-4">
                            <Card class="shadow-none border-border">
                                <CardHeader class="flex flex-row items-center justify-between">
                                    <CardTitle>Historie plateb</CardTitle>
                                    <Button @click="isPaymentDialogOpen = true" size="sm" class="h-8">
                                        <Plus class="w-4 h-4 mr-2" /> Přidat platbu
                                    </Button>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Částka</TableHead>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Datum</TableHead>
                                                <TableHead class="text-xs uppercase tracking-wider font-mono text-muted-foreground">Stav</TableHead>
                                                <TableHead class="text-right text-xs uppercase tracking-wider font-mono text-muted-foreground">Akce</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="payment in booking.payments" :key="payment.id">
                                                <TableCell class="font-medium font-mono">{{ payment.amount.formatted }}</TableCell>
                                                <TableCell>
                                                    <div v-if="payment.paid_at" class="text-xs text-muted-foreground font-mono">
                                                        {{ formatDateTime(payment.paid_at) }}
                                                    </div>
                                                    <div v-else class="text-xs text-muted-foreground">-</div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge :variant="payment.status === 'paid' ? 'outline' : 'secondary'" class="font-normal">
                                                        {{ payment.status }}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell class="text-right">
                                                     <Link :href="route('admin.bookings.payments.destroy', [booking.id, payment.id])" method="delete" as="button" class="text-muted-foreground hover:text-destructive transition-colors">
                                                        <Trash2 class="h-4 w-4" />
                                                     </Link>
                                                </TableCell>
                                            </TableRow>
                                            <TableRow v-if="booking.payments.length === 0">
                                                <TableCell colspan="4" class="text-center text-muted-foreground py-8">
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
                    <Card class="shadow-none border-border">
                        <CardHeader>
                            <CardTitle>Host</CardTitle>
                        </CardHeader>
                        <CardContent v-if="booking.customer" class="space-y-6">
                            <div class="flex items-center gap-4">
                                <Avatar class="h-9 w-9 rounded-md">
                                    <AvatarFallback class="rounded-md text-xs">{{ getInitials(booking.customer.first_name, booking.customer.last_name) }}</AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="font-medium">{{ booking.customer.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ booking.customer.email }}</p>
                                </div>
                            </div>

                            <Separator />

                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`mailto:${booking.customer.email}`" class="hover:underline">{{ booking.customer.email }}</a>
                                </div>
                                <div v-if="booking.customer.phone" class="flex items-center gap-3 text-sm">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`tel:${booking.customer.phone}`" class="hover:underline font-mono">{{ booking.customer.phone }}</a>
                                </div>
                            </div>

                            <!-- Company Info -->
                            <div v-if="booking.customer.is_company" class="rounded-md bg-muted/50 border border-border p-3 space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground mb-2">
                                    <Building2 class="h-4 w-4" />
                                    Fakturační údaje
                                </div>
                                <div class="text-sm space-y-1">
                                    <p class="font-medium">{{ booking.customer.company_name }}</p>
                                    <p v-if="booking.customer.ico" class="font-mono text-xs text-muted-foreground">IČO: {{ booking.customer.ico }}</p>
                                    <p v-if="booking.customer.dic" class="font-mono text-xs text-muted-foreground">DIČ: {{ booking.customer.dic }}</p>
                                    <div v-if="booking.customer.billing_street" class="text-xs text-muted-foreground mt-1">
                                        {{ booking.customer.billing_street }}<br>
                                        {{ booking.customer.billing_zip }} {{ booking.customer.billing_city }}<br>
                                        {{ booking.customer.billing_country }}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardContent v-else>
                            <div class="text-center py-6 text-muted-foreground">
                                <User class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p>Host není přiřazen</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Price Summary -->
                    <Card class="shadow-none border-border">
                        <CardHeader>
                            <CardTitle>Platba</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-muted-foreground">Celková cena</div>
                                    <div class="font-medium text-foreground">{{ booking.total_price.formatted }}</div>
                                </div>
                                <div>
                                    <div class="text-muted-foreground">Zaplaceno</div>
                                    <div class="font-medium text-green-600">{{ formatCurrency(paidAmount) }}</div>
                                </div>
                                <div class="col-span-2 pt-2 border-t border-border mt-2">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-muted-foreground">Zbývá doplatit</span>
                                        <span class="text-xl font-bold tracking-tight">{{ formatCurrency(remainingToPay) }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payment Status Card -->
                    <Card v-if="remainingToPay > 0">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-yellow-500" />
                                Čeká na doplatek
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Zbývá uhradit:</span>
                                <span :class="{'text-destructive': remainingToPay > 0}">
                                    {{ formatCurrency(remainingToPay) }}
                                </span>
                            </div>
                            <Button class="w-full mt-4" @click="isPaymentDialogOpen = true">
                                <Plus class="w-4 h-4 mr-2" />
                                Přidat platbu
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <AlertDialog :open="isUpdateDialogOpen" @update:open="isUpdateDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Změna stavu rezervace</AlertDialogTitle>
                    <AlertDialogDescription>
                        Opravdu chcete změnit stav této rezervace?
                        <span v-if="bookingStatusToUpdate" class="block mt-2 font-medium">
                            Nový stav: {{ BookingStatusLabels[bookingStatusToUpdate] || bookingStatusToUpdate }}
                        </span>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="bookingStatusToUpdate = null">Zrušit</AlertDialogCancel>
                    <AlertDialogAction @click="executeUpdateStatus">Potvrdit</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Smazat rezervaci</AlertDialogTitle>
                    <AlertDialogDescription>
                        Opravdu chcete smazat tuto rezervaci? Tato akce je nevratná.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Zrušit</AlertDialogCancel>
                    <AlertDialogAction class="bg-destructive hover:bg-destructive/90" @click="executeDeleteBooking">Smazat</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <Dialog :open="isPaymentDialogOpen" @update:open="isPaymentDialogOpen = $event">
            <DialogContent class="sm:max-w-[900px] p-0 overflow-hidden gap-0">
                <div class="grid grid-cols-1 md:grid-cols-2 h-full">
                    <!-- Left Side: Summary -->
                    <div class="bg-muted/30 p-6 border-r border-border flex flex-col gap-6">
                        <div>
                            <h3 class="text-lg font-semibold tracking-tight mb-6">Platba</h3>
                            
                            <div class="space-y-6">
                                 <div class="flex justify-between items-center">
                                    <span class="text-muted-foreground font-medium">Cena celkem</span>
                                    <span class="text-xl font-bold text-primary tracking-tight">{{ booking.total_price.formatted }}</span>
                                 </div>
                                 
                                 <!-- List of existing payments -->
                                 <div class="space-y-3 py-2">
                                    <div v-for="payment in booking.payments" :key="payment.id" class="flex justify-between text-sm items-center group">
                                        <div class="flex items-center gap-3 text-muted-foreground">
                                            <span class="font-mono text-xs">{{ formatDate(payment.paid_at) }}</span>
                                            <div class="flex gap-1">
                                                <FileText class="h-3 w-3" />
                                                <Trash2 @click="deletePayment(payment.id)" class="h-3 w-3 opacity-0 group-hover:opacity-100 cursor-pointer hover:text-destructive transition-opacity" />
                                            </div>
                                        </div>
                                        <span class="font-medium text-green-600 font-mono">{{ payment.amount.formatted }}</span>
                                    </div>
                                 </div>
                                 
                                 <Separator />
                                 
                                 <div class="flex justify-between items-center font-medium">
                                    <span>Zbývá doplatit</span>
                                    <span class="font-mono text-lg" :class="{'text-destructive': remainingToPay > 0}">
                                        {{ formatCurrency(remainingToPay) }}
                                    </span>
                                 </div>

                                 <div class="pt-4" v-if="paymentForm.amount">
                                     <div class="text-sm text-muted-foreground mb-2">Po zaplacení:</div>
                                     <div class="flex justify-between items-center font-medium">
                                        <span>Nový zůstatek</span>
                                        <span class="font-mono" :class="{'text-green-600': remainingAfterPayment <= 0}">
                                            {{ formatCurrency(remainingAfterPayment) }}
                                        </span>
                                     </div>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Form -->
                    <div class="p-6 flex flex-col h-full">
                        <DialogHeader class="mb-6">
                             <DialogTitle class="text-xl">Ručně potvrdit platbu</DialogTitle>
                             <DialogDescription v-if="booking.code">
                                Variabilní symbol <span class="font-mono font-medium text-foreground">{{ booking.code }}</span>
                             </DialogDescription>
                        </DialogHeader>
                        
                        <div class="space-y-5 flex-1">
                            <div class="space-y-2">
                                <Label>Datum přijetí platby</Label>
                                <Input type="date" v-model="paymentForm.paid_at" class="h-9" />
                            </div>
                            
                            <div class="space-y-2">
                                <Label>Částka</Label>
                                <div class="relative">
                                    <Input type="number" v-model="paymentForm.amount" placeholder="0" class="h-9 pr-10 font-mono" />
                                    <span class="absolute right-3 top-2 text-sm text-muted-foreground font-medium">Kč</span>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label>Poznámka</Label>
                                <Textarea v-model="paymentForm.notes" placeholder="Poznámka k platbě..." class="resize-none min-h-[100px] text-sm" />
                            </div>
                        </div>
                        
                        <DialogFooter class="mt-8 gap-2 sm:justify-end">
                            <Button variant="outline" @click="submitPayment" :disabled="paymentForm.processing">POTVRDIT A POSLAT MAIL</Button>
                            <Button @click="submitPayment" :disabled="paymentForm.processing">POTVRDIT</Button>
                        </DialogFooter>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>