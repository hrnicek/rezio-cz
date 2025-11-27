<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
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
import AppDataTable from '@/components/AppDataTable.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';
import { Eye, Check, X } from 'lucide-vue-next';
import { BookingStatusLabels } from '@/lib/enums';

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

const breadcrumbs = [
    { title: 'Rezervace', href: route('admin.bookings.index') },
];

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
    if (confirm(`Opravdu chcete označit tuto rezervaci jako "${BookingStatusLabels[status] || status}"?`)) {
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

const columns = [
    { key: 'property', label: 'Nemovitost' },
    { key: 'customer', label: 'Host' },
    { key: 'dates', label: 'Termín' },
    { key: 'total_price', label: 'Cena celkem' },
    { key: 'status', label: 'Stav' },
    { key: 'actions', label: 'Akce', align: 'right' as const },
];
</script>

<template>
    <Head title="Rezervace" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-64">
                        <Input 
                            v-model="searchQuery" 
                            placeholder="Hledat hosty..." 
                            class="w-full h-9"
                        />
                    </div>
                    <div class="w-48">
                        <Select v-model="statusFilter">
                            <SelectTrigger class="h-9">
                                <SelectValue placeholder="Filtrovat dle stavu" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Všechny stavy</SelectItem>
                                <SelectItem v-for="(label, value) in BookingStatusLabels" :key="value" :value="value">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <Button variant="outline" size="sm" class="h-9" @click="exportBookings">
                    Exportovat CSV
                </Button>
            </div>

            <AppDataTable 
                :data="bookings" 
                :columns="columns"
                no-results-message="Žádné rezervace nenalezeny."
            >
                <template #property="{ item }">
                    <span class="font-medium">{{ item.property.name }}</span>
                </template>
                
                <template #customer="{ item }">
                    <div v-if="item.customer">
                        <div>{{ item.customer.first_name }} {{ item.customer.last_name }}</div>
                        <div class="text-xs text-muted-foreground">{{ item.customer.email }}</div>
                    </div>
                    <div v-else class="text-muted-foreground italic">
                        Žádné info o hostovi
                    </div>
                </template>
                
                <template #dates="{ item }">
                    {{ item.start_date }} - {{ item.end_date }}
                </template>
                
                <template #total_price="{ item }">
                    {{ formatCurrency(item.total_price) }}
                </template>
                
                <template #status="{ item }">
                    <Badge :variant="getStatusVariant(item.status)">
                        {{ BookingStatusLabels[item.status] || item.status }}
                    </Badge>
                </template>
                
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" size="icon-sm" as-child>
                            <Link :href="route('admin.bookings.show', item.id)">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button 
                            v-if="item.status === 'pending'" 
                            variant="default"
                            size="icon-sm" 
                            @click="updateStatus(item.id, 'confirmed')"
                            title="Potvrdit"
                        >
                            <Check class="h-4 w-4" />
                        </Button>
                        <Button 
                            v-if="item.status !== 'cancelled'" 
                            variant="destructive" 
                            size="icon-sm" 
                            @click="updateStatus(item.id, 'cancelled')"
                            title="Zrušit"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>
        </div>
    </AppLayout>
</template>
