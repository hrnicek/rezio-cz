<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  BarElement,
} from 'chart.js';
import { Bar } from 'vue-chartjs';
import axios from 'axios';

declare const route: any;

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend
);

defineProps<{
    properties: Array<{ id: number; name: string }>;
}>();

interface ChartDataItem {
    date: string;
    revenue: number;
    occupancy: number;
}

interface ReportStats {
    total_revenue: number;
    total_bookings: number;
    occupancy_rate: number;
    adr: number;
    revpar: number;
    chart_data: ChartDataItem[];
}

const startDate = ref(new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);
const selectedProperty = ref<string>('all');

const loading = ref(false);
const stats = ref<Omit<ReportStats, 'chart_data'>>({
    total_revenue: 0,
    total_bookings: 0,
    occupancy_rate: 0,
    adr: 0,
    revpar: 0,
});

const chartData = ref<{
    labels: string[];
    datasets: {
        label: string;
        backgroundColor: string;
        borderColor: string;
        data: number[];
    }[];
}>({
    labels: [],
    datasets: [],
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};

const exportReport = () => {
    const url = route('admin.reports.export', {
        start_date: startDate.value,
        end_date: endDate.value,
        property_id: selectedProperty.value === 'all' ? null : selectedProperty.value,
    });
    window.location.href = url;
};

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get<ReportStats>(route('admin.reports.data'), {
            params: {
                start_date: startDate.value,
                end_date: endDate.value,
                property_id: selectedProperty.value === 'all' ? null : selectedProperty.value,
            },
        });

        stats.value = {
            total_revenue: response.data.total_revenue,
            total_bookings: response.data.total_bookings,
            occupancy_rate: response.data.occupancy_rate,
            adr: response.data.adr,
            revpar: response.data.revpar,
        };

        chartData.value = {
            labels: response.data.chart_data.map((d) => d.date),
            datasets: [
                {
                    label: 'Denní tržby',
                    backgroundColor: '#3b82f6',
                    borderColor: '#3b82f6',
                    data: response.data.chart_data.map((d) => d.revenue),
                },
            ],
        };
    } catch (error) {
        console.error('Error fetching report data:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

watch([startDate, endDate, selectedProperty], () => {
    fetchData();
});

const breadcrumbs = [
    {
        title: 'Reporty',
        href: '/admin/reports',
    },
];
</script>

<template>
    <Head title="Reporty" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-foreground">Přehledy a Reporty</h2>
            </div>

            <!-- Filters -->
            <div class="grid gap-4 md:grid-cols-5 items-end">
                <div class="space-y-2">
                    <Label>Nemovitost</Label>
                    <Select v-model="selectedProperty">
                        <SelectTrigger class="h-9">
                            <SelectValue placeholder="Všechny nemovitosti" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Všechny nemovitosti</SelectItem>
                            <SelectItem v-for="property in properties" :key="property.id" :value="property.id.toString()">
                                {{ property.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label>Od</Label>
                    <Input type="date" v-model="startDate" class="h-9" />
                </div>
                <div class="space-y-2">
                    <Label>Do</Label>
                    <Input type="date" v-model="endDate" class="h-9" />
                </div>
                <div class="flex items-end">
                    <Button @click="fetchData" :disabled="loading" class="w-full h-9">
                        {{ loading ? 'Načítání...' : 'Obnovit' }}
                    </Button>
                </div>
                <div class="flex items-end">
                    <Button variant="outline" @click="exportReport" :disabled="loading" class="w-full h-9">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Exportovat CSV
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Celkové tržby</CardTitle>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            class="h-4 w-4 text-muted-foreground"
                        >
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: 'CZK' }).format(stats.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Za vybrané období
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rezervace</CardTitle>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            class="h-4 w-4 text-muted-foreground"
                        >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">+{{ stats.total_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            Potvrzené rezervace
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Obsazenost</CardTitle>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            class="h-4 w-4 text-muted-foreground"
                        >
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                            <line x1="16" x2="16" y1="2" y2="6" />
                            <line x1="8" x2="8" y1="2" y2="6" />
                            <line x1="3" x2="21" y1="10" y2="10" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.occupancy_rate }}%</div>
                        <p class="text-xs text-muted-foreground">
                            Z dostupných nocí
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">ADR</CardTitle>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            class="h-4 w-4 text-muted-foreground"
                        >
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: 'CZK' }).format(stats.adr) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Průměrná cena za noc
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">RevPAR</CardTitle>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            class="h-4 w-4 text-muted-foreground"
                        >
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                            <polyline points="16 7 22 7 22 13" />
                        </svg>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: 'CZK' }).format(stats.revpar) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Výnos na dostupný pokoj
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid gap-4 md:grid-cols-1">
                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>Přehled tržeb</CardTitle>
                    </CardHeader>
                    <CardContent class="pl-2">
                        <div class="h-[350px]">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
