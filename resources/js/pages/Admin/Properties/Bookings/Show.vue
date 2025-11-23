<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Calendar, CreditCard, User, Mail, Phone, MapPin, ExternalLink, Copy, CheckCircle2 } from 'lucide-vue-next';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

declare const route: any;

const props = defineProps<{
    booking: any;
}>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('cs-CZ');
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
        case 'confirmed':
            return 'bg-green-500 hover:bg-green-600';
        case 'pending':
            return 'bg-yellow-500 hover:bg-yellow-600';
        case 'cancelled':
            return 'bg-red-500 hover:bg-red-600';
        default:
            return 'bg-gray-500 hover:bg-gray-600';
    }
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        confirmed: 'Potvrzeno',
        pending: 'Čeká na potvrzení',
        cancelled: 'Zrušeno',
    };
    return labels[status] || status;
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    // You might want to add a toast notification here
};
</script>

<template>
    <AppLayout>
        <Head title="Detail rezervace" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.bookings.index')">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                    </Link>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight">Rezervace {{ booking.code }}</h2>
                        <p class="text-muted-foreground">
                            Vytvořeno {{ formatDate(booking.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Badge :class="getStatusColor(booking.status)">
                        {{ getStatusLabel(booking.status) }}
                    </Badge>
                </div>
            </div>

            <Tabs default-value="info" class="w-full">
                <TabsList>
                    <TabsTrigger value="info">Hlavní informace</TabsTrigger>
                    <TabsTrigger value="checkin">Check-in</TabsTrigger>
                    <TabsTrigger value="payments">Platby</TabsTrigger>
                </TabsList>

                <TabsContent value="info" class="space-y-6 mt-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Info Column -->
                        <div class="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Detaily pobytu</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Termín</p>
                                            <div class="flex items-center">
                                                <Calendar class="mr-2 h-4 w-4 text-muted-foreground" />
                                                <span>{{ formatDate(booking.start_date) }} - {{ formatDate(booking.end_date) }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Nemovitost</p>
                                            <div class="flex items-center">
                                                <MapPin class="mr-2 h-4 w-4 text-muted-foreground" />
                                                <span>{{ booking.property.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <Separator />
                                    
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-muted-foreground">Poznámka</p>
                                        <p class="text-sm">{{ booking.notes || 'Bez poznámky' }}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Host</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Jméno</p>
                                            <div class="flex items-center">
                                                <User class="mr-2 h-4 w-4 text-muted-foreground" />
                                                <span>{{ booking.customer?.first_name }} {{ booking.customer?.last_name }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                                            <div class="flex items-center">
                                                <Mail class="mr-2 h-4 w-4 text-muted-foreground" />
                                                <a :href="'mailto:' + booking.customer?.email" class="hover:underline">
                                                    {{ booking.customer?.email }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-muted-foreground">Telefon</p>
                                            <div class="flex items-center">
                                                <Phone class="mr-2 h-4 w-4 text-muted-foreground" />
                                                <a :href="'tel:' + booking.customer?.phone" class="hover:underline">
                                                    {{ booking.customer?.phone }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Sidebar Column -->
                        <div class="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Cena</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-3xl font-bold">
                                        {{ formatCurrency(booking.total_price) }}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="checkin" class="space-y-6 mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Online Check-in</CardTitle>
                            <CardDescription>Správa online check-inu a hostů</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-if="booking.checkin_token" class="flex items-center space-x-2 p-4 bg-muted rounded-lg">
                                <div class="flex-1 truncate font-mono text-sm">
                                    {{ route('check-in.show', booking.checkin_token) }}
                                </div>
                                <Button variant="ghost" size="icon" @click="copyToClipboard(route('check-in.show', booking.checkin_token))">
                                    <Copy class="w-4 h-4" />
                                </Button>
                                <a :href="route('check-in.show', booking.checkin_token)" target="_blank">
                                    <Button variant="ghost" size="icon">
                                        <ExternalLink class="w-4 h-4" />
                                    </Button>
                                </a>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Seznam hostů ({{ booking.guests.length }})</h3>
                                <div v-if="booking.guests.length > 0" class="border rounded-lg divide-y">
                                    <div v-for="guest in booking.guests" :key="guest.id" class="p-4 flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <User class="w-4 h-4 text-muted-foreground" />
                                            <div>
                                                <p class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</p>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ guest.is_adult ? 'Dospělý' : 'Dítě' }} • {{ guest.nationality || 'Nezadáno' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="guest.document_number" class="text-sm text-muted-foreground">
                                            {{ guest.document_type === 'passport' ? 'Pas' : 'OP' }}: {{ guest.document_number }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8 text-muted-foreground">
                                    Zatím nebyli přidáni žádní hosté.
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="payments" class="space-y-6 mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Platby</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Částka</TableHead>
                                        <TableHead>Datum splatnosti</TableHead>
                                        <TableHead>Zaplaceno dne</TableHead>
                                        <TableHead>Stav</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="payment in booking.payments" :key="payment.id">
                                        <TableCell class="font-medium">{{ formatCurrency(payment.amount) }}</TableCell>
                                        <TableCell>{{ formatDate(payment.due_date) }}</TableCell>
                                        <TableCell>{{ payment.paid_at ? formatDate(payment.paid_at) : '-' }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="payment.status === 'paid' ? 'default' : 'secondary'">
                                                {{ payment.status === 'paid' ? 'Zaplaceno' : 'Čeká na platbu' }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="booking.payments.length === 0">
                                        <TableCell colspan="4" class="text-center text-muted-foreground">
                                            Žádné platby
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
