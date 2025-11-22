<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

declare const route: any;

const props = defineProps<{
    bookings: {
        data: Array<{
            id: number;
            property: { name: string };
            customer: { first_name: string; last_name: string; email: string; phone: string } | null;
            start_date: string;
            end_date: string;
            total_price: number;
            status: string;
            notes?: string;
        }>;
        links: any;
        meta: any;
    };
    filters: {
        status?: string;
        search?: string;
    };
}>();

const statusFilter = ref(props.filters.status || 'all');
const searchQuery = ref(props.filters.search || '');

const updateFilters = debounce(() => {
    router.get(route('admin.bookings.index'), { 
        status: statusFilter.value === 'all' ? null : statusFilter.value,
        search: searchQuery.value || null
    }, { preserveState: true, replace: true });
}, 300);

watch(statusFilter, updateFilters);
watch(searchQuery, updateFilters);

const exportBookings = () => {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all') params.append('status', statusFilter.value);
    if (searchQuery.value) params.append('search', searchQuery.value);
    
    window.location.href = route('admin.bookings.export') + '?' + params.toString();
};

const updateStatus = (bookingId: number, status: string) => {
    if (confirm(`Opravdu chcete označit tuto rezervaci jako ${status}?`)) {
        router.put(route('admin.bookings.update', bookingId), { status });
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'confirmed': return 'default'; // primary color
        case 'pending': return 'secondary'; // gray
        case 'cancelled': return 'destructive'; // red
        case 'paid': return 'outline'; // green-ish usually, but outline for now
        default: return 'secondary';
    }
};
</script>

<template>
    <Head title="Rezervace" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Rezervace
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6 gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-48">
                                    <Select v-model="statusFilter">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Filtrovat dle stavu" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Všechny stavy</SelectItem>
                                            <SelectItem value="pending">Čekající</SelectItem>
                                            <SelectItem value="confirmed">Potvrzeno</SelectItem>
                                            <SelectItem value="cancelled">Zrušeno</SelectItem>
                                            <SelectItem value="paid">Zaplaceno</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="w-64">
                                    <Input 
                                        v-model="searchQuery" 
                                        placeholder="Hledat hosty..." 
                                        class="w-full"
                                    />
                                </div>
                            </div>
                            <Button variant="outline" @click="exportBookings">
                                Exportovat CSV
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nemovitost</TableHead>
                                    <TableHead>Host</TableHead>
                                    <TableHead>Termín</TableHead>
                                    <TableHead>Cena celkem</TableHead>
                                    <TableHead>Stav</TableHead>
                                    <TableHead class="text-right">Akce</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="booking in bookings.data" :key="booking.id">
                                    <TableCell class="font-medium">{{ booking.property.name }}</TableCell>
                                    <TableCell>
                                        <div v-if="booking.customer">
                                            <div>{{ booking.customer.first_name }} {{ booking.customer.last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ booking.customer.email }}</div>
                                        </div>
                                        <div v-else class="text-gray-400 italic">
                                            Žádné info o hostovi
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ booking.start_date }} - {{ booking.end_date }}
                                    </TableCell>
                                    <TableCell>${{ booking.total_price }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(booking.status)">
                                            {{ booking.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right space-x-2">
                                        <Button 
                                            v-if="booking.status === 'pending'" 
                                            size="sm" 
                                            @click="updateStatus(booking.id, 'confirmed')"
                                        >
                                            Potvrdit
                                        </Button>
                                        <Button 
                                            v-if="booking.status !== 'cancelled'" 
                                            variant="destructive" 
                                            size="sm" 
                                            @click="updateStatus(booking.id, 'cancelled')"
                                        >
                                            Zrušit
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="bookings.data.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                        Žádné rezervace nenalezeny.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
