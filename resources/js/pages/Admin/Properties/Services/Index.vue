<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    services: Array<{
        id: number;
        name: string;
        description: string | null;
        price_type: 'per_day' | 'flat' | 'per_stay';
        price: number;
        max_quantity: number;
        is_active: boolean;
    }>;
}>();

const editingId = ref<number | null>(null);
const isAdding = ref(false);

const form = useForm({
    name: '',
    description: '',
    price_type: 'flat',
    price: 0,
    max_quantity: 0,
    is_active: true,
});

const startAdding = () => {
    isAdding.value = true;
    editingId.value = null;
    form.reset();
};

const startEditing = (service: any) => {
    isAdding.value = false;
    editingId.value = service.id;
    form.name = service.name;
    form.description = service.description || '';
    form.price_type = service.price_type;
    form.price = service.price;
    form.max_quantity = service.max_quantity;
    form.is_active = !!service.is_active;
};

const cancelEdit = () => {
    isAdding.value = false;
    editingId.value = null;
    form.reset();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.properties.services.update', [props.property.id, editingId.value]), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    } else {
        form.post(route('admin.properties.services.store', props.property.id), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    }
};

const deleteService = (serviceId: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
        router.delete(route('admin.properties.services.destroy', [props.property.id, serviceId]));
    }
};

const breadcrumbs = [
    { title: 'Properties', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Services', href: `/admin/properties/${props.property.id}/services` },
];

const getPriceTypeLabel = (type: string) => {
    switch (type) {
        case 'per_day': return 'Per Day';
        case 'flat': return 'Flat Fee';
        case 'per_stay': return 'Per Stay';
        default: return type;
    }
};
</script>

<template>
    <Head :title="`Services - ${property.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('admin.properties.edit', property.id)">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h2 class="text-2xl font-bold tracking-tight">Services - {{ property.name }}</h2>
                </div>
                <Button @click="startAdding" v-if="!isAdding && !editingId">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Service
                </Button>
            </div>

            <!-- Add/Edit Form -->
            <Card v-if="isAdding || editingId">
                <CardHeader>
                    <CardTitle>{{ editingId ? 'Edit Service' : 'Add New Service' }}</CardTitle>
                    <CardDescription>
                        {{ editingId ? 'Update service details' : 'Create a new service for this property' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g. Breakfast" required />
                                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Price (Kč)</Label>
                                <Input id="price" v-model.number="form.price" type="number" step="0.01" required />
                                <div v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="price_type">Price Type</Label>
                                <Select v-model="form.price_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select price type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="flat">Flat Fee</SelectItem>
                                        <SelectItem value="per_day">Per Day</SelectItem>
                                        <SelectItem value="per_stay">Per Stay</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.price_type" class="text-sm text-red-500">{{ form.errors.price_type }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="max_quantity">Max Quantity (0 for unlimited)</Label>
                                <Input id="max_quantity" v-model.number="form.max_quantity" type="number" min="0" required />
                                <div v-if="form.errors.max_quantity" class="text-sm text-red-500">{{ form.errors.max_quantity }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Service description..." />
                            <div v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">Active</Label>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="cancelEdit">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ editingId ? 'Update' : 'Create' }} Service
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Services Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Existing Services</CardTitle>
                    <CardDescription>
                        Manage services available for this property
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Max Qty</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="service in services" :key="service.id">
                                <TableCell class="font-medium">
                                    <div>{{ service.name }}</div>
                                    <div class="text-xs text-muted-foreground truncate max-w-[200px]">{{ service.description }}</div>
                                </TableCell>
                                <TableCell>{{ service.price }} Kč</TableCell>
                                <TableCell>{{ getPriceTypeLabel(service.price_type) }}</TableCell>
                                <TableCell>{{ service.max_quantity === 0 ? 'Unlimited' : service.max_quantity }}</TableCell>
                                <TableCell>
                                    <Badge :variant="service.is_active ? 'default' : 'secondary'">
                                        {{ service.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button size="sm" variant="outline" @click="startEditing(service)">
                                            <Pencil class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="destructive" @click="deleteService(service.id)">
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="services.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                    No services created yet. Click "Add Service" to create one.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
