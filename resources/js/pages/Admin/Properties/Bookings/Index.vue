<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
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
import { useCurrency } from '@/composables/useCurrency';
import { Eye, Check, X } from 'lucide-vue-next';

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
const { formatCurrency } = useCurrency();

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

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Rezervace</h2>
                <Button variant="outline" @click="exportBookings">
                    Exportovat CSV
                </Button>
            </div>

            <div class="flex items-center gap-4 mb-4">
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

            <div class="rounded-md border">
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
                                    <TableCell>{{ formatCurrency(booking.total_price) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(booking.status)">
                                            {{ booking.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="icon" as-child>
                                                <Link :href="route('admin.bookings.show', booking.id)">
                                                    <Eye class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button 
                                                v-if="booking.status === 'pending'" 
                                                variant="default"
                                                size="icon" 
                                                @click="updateStatus(booking.id, 'confirmed')"
                                                title="Potvrdit"
                                            >
                                                <Check class="h-4 w-4" />
                                            </Button>
                                            <Button 
                                                v-if="booking.status !== 'cancelled'" 
                                                variant="destructive" 
                                                size="icon" 
                                                @click="updateStatus(booking.id, 'cancelled')"
                                                title="Zrušit"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
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
        </AppLayout>
</template>
