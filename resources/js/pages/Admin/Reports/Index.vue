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
import { Line, Bar } from 'vue-chartjs';
import axios from 'axios';

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

const props = defineProps<{
    properties: Array<{ id: number; name: string }>;
}>();

const startDate = ref(new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);
const selectedProperty = ref<string | null>('all');

const loading = ref(false);
const stats = ref({
    total_revenue: 0,
    total_bookings: 0,
    occupancy_rate: 0,
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

const fetchData = async () => {
    loading.value = true;
    try {
        // @ts-ignore
        const response = await axios.get(route('admin.reports.data'), {
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
        };

        chartData.value = {
            labels: response.data.chart_data.map((d: any) => d.date),
            datasets: [
                {
                    label: 'Daily Revenue',
                    backgroundColor: '#3b82f6',
                    borderColor: '#3b82f6',
                    data: response.data.chart_data.map((d: any) => d.revenue),
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
        title: 'Reports',
        href: '/admin/reports',
    },
];
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Reporting Dashboard</h2>
            </div>

            <!-- Filters -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2">
                    <Label>Property</Label>
                    <Select v-model="selectedProperty">
                        <SelectTrigger>
                            <SelectValue placeholder="All Properties" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Properties</SelectItem>
                            <SelectItem v-for="property in properties" :key="property.id" :value="property.id.toString()">
                                {{ property.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label>Start Date</Label>
                    <Input type="date" v-model="startDate" />
                </div>
                <div class="space-y-2">
                    <Label>End Date</Label>
                    <Input type="date" v-model="endDate" />
                </div>
                <div class="flex items-end">
                    <Button @click="fetchData" :disabled="loading" class="w-full">
                        {{ loading ? 'Loading...' : 'Refresh' }}
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
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
                            For selected period
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Bookings</CardTitle>
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
                            Confirmed bookings
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Occupancy Rate</CardTitle>
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
                            Of available nights
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid gap-4 md:grid-cols-1">
                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>Revenue Overview</CardTitle>
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
