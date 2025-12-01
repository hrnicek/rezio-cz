<script setup lang="ts">
import PropertyLayout from '../Partials/PropertyLayout.vue';
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
import { Eye, Check, X, Calendar } from 'lucide-vue-next';
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

// Matches App\Data\Admin\Booking\BookingListData
interface BookingListItem {
    id: string;
    code: string | null;
    status: string;
    status_label: string;
    customer_name: string;
    check_in_date: string;
    check_out_date: string;
    total_price: {
        amount: number;
        currency: string;
        formatted: number;
        display: string;
    };
    created_at_human: string;
}

const props = defineProps<{
    property: { id: number; name: string } | null;
    bookings: {
        data: BookingListItem[];
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
const bookingToUpdate = ref<{ id: string, status: string } | null>(null);
const isUpdateDialogOpen = ref(false);

const breadcrumbs = [
    { title: 'Nemovitosti', href: route('admin.properties.index') },
    ...(props.property ? [
        { title: props.property.name, href: route('admin.properties.edit', props.property.id) },
        { title: 'Rezervace', href: route('admin.properties.bookings.index', props.property.id) }
    ] : [
        { title: 'Rezervace', href: route('admin.bookings.index') }
    ]),
];

const updateFilters = debounce(() => {
    const routeName = props.property
        ? route('admin.properties.bookings.index', props.property.id)
        : route('admin.bookings.index');
    router.get(routeName, {
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
    
    // Using appropriate export route
    const exportRoute = props.property
        ? route('admin.properties.bookings.export', props.property.id)
        : route('admin.bookings.export');
    window.location.href = exportRoute + '?' + params.toString();
};

const confirmUpdateStatus = (bookingId: string, status: string) => {
    bookingToUpdate.value = { id: bookingId, status };
    isUpdateDialogOpen.value = true;
};

const executeUpdateStatus = () => {
    if (!bookingToUpdate.value) return;
    
    const { id, status } = bookingToUpdate.value;
    
    router.put(route('admin.bookings.update', id), { status }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Stav rezervace byl úspěšně změněn.');
            isUpdateDialogOpen.value = false;
            bookingToUpdate.value = null;
        },
        onError: () => {
            toast.error('Nepodařilo se změnit stav rezervace.');
        }
    });
};

const getStatusVariant = (status: string) => {
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

const columns = [
    { key: 'customer', label: 'Host' },
    { key: 'dates', label: 'Termín' },
    { key: 'total_price', label: 'Cena celkem' },
    { key: 'status', label: 'Stav' },
    { key: 'actions', label: 'Akce', align: 'right' as const },
];
</script>

<template>
    <Head :title="property ? `Rezervace - ${property.name}` : 'Rezervace'" />

    <PropertyLayout v-if="property" :property="property" :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Rezervace</h2>
                    <p class="text-muted-foreground">Přehled a správa rezervací.</p>
                </div>
                <Button variant="outline" size="sm" class="h-9 shadow-sm" @click="exportBookings">
                    Exportovat CSV
                </Button>
            </div>

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

            <AppDataTable 
                :data="bookings" 
                :columns="columns"
                no-results-message="Žádné rezervace nenalezeny."
            >
                <template #customer="{ item }">
                    <div class="font-medium text-foreground">{{ item.customer_name }}</div>
                    <div class="text-xs text-muted-foreground font-mono">{{ item.code }}</div>
                </template>
                
                <template #dates="{ item }">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-3 w-3 text-muted-foreground" />
                        <span class="text-sm font-medium">{{ item.check_in_date }} - {{ item.check_out_date }}</span>
                    </div>
                </template>
                
                <template #total_price="{ item }">
                    <span class="font-mono font-medium">{{ item.total_price.display }}</span>
                </template>
                
                <template #status="{ item }">
                    <Badge :variant="getStatusVariant(item.status)" class="rounded-sm px-2 py-0.5 font-normal">
                        {{ item.status_label }}
                    </Badge>
                </template>
                
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('admin.bookings.show', item.id)">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button 
                            v-if="item.status === 'pending'" 
                            variant="outline"
                            size="icon"
                            class="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50 border-green-200"
                            @click="confirmUpdateStatus(item.id, 'confirmed')"
                            title="Potvrdit"
                        >
                            <Check class="h-4 w-4" />
                        </Button>
                        <Button 
                            v-if="['pending', 'confirmed'].includes(item.status)" 
                            variant="ghost" 
                            size="icon" 
                            class="h-8 w-8 text-muted-foreground hover:text-destructive"
                            @click="confirmUpdateStatus(item.id, 'cancelled')"
                            title="Zrušit"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>

            <AlertDialog :open="isUpdateDialogOpen" @update:open="isUpdateDialogOpen = $event">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Změna stavu rezervace</AlertDialogTitle>
                        <AlertDialogDescription>
                            Opravdu chcete změnit stav této rezervace?
                            <span v-if="bookingToUpdate" class="block mt-2 font-medium">
                                Nový stav: {{ BookingStatusLabels[bookingToUpdate.status] || bookingToUpdate.status }}
                            </span>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="bookingToUpdate = null">Zrušit</AlertDialogCancel>
                        <AlertDialogAction @click="executeUpdateStatus">Potvrdit</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </PropertyLayout>
    <AppLayout v-else :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Rezervace</h2>
                    <p class="text-muted-foreground">Přehled a správa rezervací.</p>
                </div>
                <Button variant="outline" size="sm" class="h-9 shadow-sm" @click="exportBookings">
                    Exportovat CSV
                </Button>
            </div>

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

            <AppDataTable
                :data="bookings"
                :columns="columns"
                no-results-message="Žádné rezervace nenalezeny."
            >
                <template #customer="{ item }">
                    <div class="font-medium text-foreground">{{ item.customer_name }}</div>
                    <div class="text-xs text-muted-foreground font-mono">{{ item.code }}</div>
                </template>

                <template #dates="{ item }">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-3 w-3 text-muted-foreground" />
                        <span class="text-sm font-medium">{{ item.check_in_date }} - {{ item.check_out_date }}</span>
                    </div>
                </template>

                <template #total_price="{ item }">
                    <span class="font-mono font-medium">{{ item.total_price.display }}</span>
                </template>

                <template #status="{ item }">
                    <Badge :variant="getStatusVariant(item.status)" class="rounded-sm px-2 py-0.5 font-normal">
                        {{ item.status_label }}
                    </Badge>
                </template>

                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('admin.bookings.show', item.id)">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            v-if="item.status === 'pending'"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50 border-green-200"
                            @click="confirmUpdateStatus(item.id, 'confirmed')"
                            title="Potvrdit"
                        >
                            <Check class="h-4 w-4" />
                        </Button>
                        <Button
                            v-if="['pending', 'confirmed'].includes(item.status)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-muted-foreground hover:text-destructive"
                            @click="confirmUpdateStatus(item.id, 'cancelled')"
                            title="Zrušit"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>

            <AlertDialog :open="isUpdateDialogOpen" @update:open="isUpdateDialogOpen = $event">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Změna stavu rezervace</AlertDialogTitle>
                        <AlertDialogDescription>
                            Opravdu chcete změnit stav této rezervace?
                            <span v-if="bookingToUpdate" class="block mt-2 font-medium">
                                Nový stav: {{ BookingStatusLabels[bookingToUpdate.status] || bookingToUpdate.status }}
                            </span>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="bookingToUpdate = null">Zrušit</AlertDialogCancel>
                        <AlertDialogAction @click="executeUpdateStatus">Potvrdit</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>
