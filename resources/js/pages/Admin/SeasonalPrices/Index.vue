<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { ChevronLeft, Pencil, Trash } from 'lucide-vue-next';

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    seasonalPrices: Array<{
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        price_per_night: number;
    }>;
}>();

interface SeasonalPrice {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    price_per_night: number;
}

const isDialogOpen = ref(false);
const editingPrice = ref<SeasonalPrice | null>(null);

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    price_per_night: '',
});

const openCreateDialog = () => {
    editingPrice.value = null;
    form.reset();
    form.clearErrors();
    isDialogOpen.value = true;
};

const openEditDialog = (price: SeasonalPrice) => {
    editingPrice.value = price;
    form.name = price.name;
    form.start_date = price.start_date;
    form.end_date = price.end_date;
    form.price_per_night = price.price_per_night.toString();
    form.clearErrors();
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingPrice.value) {
        form.put(route('admin.properties.seasonal-prices.update', [props.property.id, editingPrice.value.id]), {
            onSuccess: () => isDialogOpen.value = false,
        });
    } else {
        form.post(route('admin.properties.seasonal-prices.store', props.property.id), {
            onSuccess: () => isDialogOpen.value = false,
        });
    }
};

const deletePrice = (price: SeasonalPrice) => {
    if (confirm('Are you sure you want to delete this seasonal price?')) {
        router.delete(route('admin.properties.seasonal-prices.destroy', [props.property.id, price.id]));
    }
};

const breadcrumbs = [
    {
        title: 'Properties',
        href: '/admin/properties',
    },
    {
        title: props.property.name,
        href: `/admin/properties/${props.property.id}/edit`,
    },
    {
        title: 'Seasonal Prices',
        href: `/admin/properties/${props.property.id}/seasonal-prices`,
    },
];
</script>

<template>
    <Head title="Seasonal Prices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('admin.properties.edit', property.id)">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h2 class="text-2xl font-bold tracking-tight">Seasonal Prices</h2>
                </div>
                <Button @click="openCreateDialog">Add Price</Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Manage Seasonal Prices</CardTitle>
                    <CardDescription>
                        Set different prices for specific date ranges. Overlapping dates are not allowed.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Start Date</TableHead>
                                <TableHead>End Date</TableHead>
                                <TableHead>Price / Night</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="price in seasonalPrices" :key="price.id">
                                <TableCell class="font-medium">{{ price.name }}</TableCell>
                                <TableCell>{{ price.start_date }}</TableCell>
                                <TableCell>{{ price.end_date }}</TableCell>
                                <TableCell>{{ price.price_per_night }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" @click="openEditDialog(price)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deletePrice(price)">
                                            <Trash class="h-4 w-4 text-red-500" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="seasonalPrices.length === 0">
                                <TableCell colspan="5" class="text-center text-muted-foreground">
                                    No seasonal prices found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Dialog v-model:open="isDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ editingPrice ? 'Edit Seasonal Price' : 'Add Seasonal Price' }}</DialogTitle>
                        <DialogDescription>
                            Define the name, dates, and price for this season.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="e.g. Summer 2025" required />
                            <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" type="date" v-model="form.start_date" required />
                                <div v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</div>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_date">End Date</Label>
                                <Input id="end_date" type="date" v-model="form.end_date" required />
                                <div v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="price">Price per Night</Label>
                            <Input id="price" type="number" step="0.01" v-model="form.price_per_night" required />
                            <div v-if="form.errors.price_per_night" class="text-sm text-red-500">{{ form.errors.price_per_night }}</div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">
                                {{ editingPrice ? 'Update' : 'Create' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
