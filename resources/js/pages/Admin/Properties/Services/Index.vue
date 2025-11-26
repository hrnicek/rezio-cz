<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref, h } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppDataTable from '@/components/AppDataTable.vue';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    services: {
        data: Array<{
            id: number;
            name: string;
            description: string | null;
            price_type: 'per_day' | 'flat' | 'per_stay';
            price: number;
            max_quantity: number;
            is_active: boolean;
        }>;
        links: any[];
        meta: any;
    };
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
    if (confirm('Opravdu chcete smazat tuto službu?')) {
        router.delete(route('admin.properties.services.destroy', [props.property.id, serviceId]));
    }
};

const breadcrumbs = [
    { title: 'Nemovitosti', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Služby', href: `/admin/properties/${props.property.id}/services` },
];

const getPriceTypeLabel = (type: string) => {
    switch (type) {
        case 'per_day': return 'Za den';
        case 'flat': return 'Fixní poplatek';
        case 'per_stay': return 'Za pobyt';
        default: return type;
    }
};

const columns = [
    {
        key: 'name',
        label: 'Název',
        sortable: true
    },
    {
        key: 'price',
        label: 'Cena',
        sortable: true
    },
    {
        key: 'price_type',
        label: 'Typ',
        sortable: true
    },
    {
        key: 'max_quantity',
        label: 'Max. množství',
        sortable: true
    },
    {
        key: 'is_active',
        label: 'Stav',
        sortable: true
    },
    {
        key: 'actions',
        label: 'Akce',
        align: 'right' as const
    }
];
</script>

<template>
    <Head :title="`Služby - ${property.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('admin.properties.edit', property.id)">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h2 class="text-2xl font-bold tracking-tight">Služby - {{ property.name }}</h2>
                </div>
                <Button @click="startAdding" v-if="!isAdding && !editingId">
                    <Plus class="mr-2 h-4 w-4" />
                    Přidat službu
                </Button>
            </div>

            <!-- Add/Edit Form -->
            <Card v-if="isAdding || editingId" class="mb-6 border-2 border-primary/20">
                <CardHeader>
                    <CardTitle>{{ editingId ? 'Upravit službu' : 'Přidat novou službu' }}</CardTitle>
                    <CardDescription>
                        {{ editingId ? 'Úprava detailů služby' : 'Vytvoření nové doplňkové služby pro tuto nemovitost' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Název</Label>
                                <Input id="name" v-model="form.name" placeholder="např. Snídaně" required />
                                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Cena (Kč)</Label>
                                <Input id="price" v-model.number="form.price" type="number" step="0.01" required />
                                <div v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="price_type">Typ ceny</Label>
                                <Select v-model="form.price_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Vyberte typ ceny" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="flat">Fixní poplatek</SelectItem>
                                        <SelectItem value="per_day">Za den</SelectItem>
                                        <SelectItem value="per_stay">Za pobyt</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.price_type" class="text-sm text-red-500">{{ form.errors.price_type }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="max_quantity">Max. množství (0 pro neomezeně)</Label>
                                <Input id="max_quantity" v-model.number="form.max_quantity" type="number" min="0" required />
                                <div v-if="form.errors.max_quantity" class="text-sm text-red-500">{{ form.errors.max_quantity }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Popis</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Popis služby..." />
                            <div v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">Aktivní</Label>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="cancelEdit">
                                Zrušit
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ editingId ? 'Upravit' : 'Vytvořit' }} službu
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Services Table -->
            <AppDataTable
                :data="services"
                :columns="columns"
                search-placeholder="Hledat služby..."
            >
                <template #name="{ item }">
                    <div class="font-medium">{{ item.name }}</div>
                    <div v-if="item.description" class="text-xs text-muted-foreground truncate max-w-[200px]">{{ item.description }}</div>
                </template>
                <template #price="{ value }">
                    {{ value }} Kč
                </template>
                <template #price_type="{ value }">
                    {{ getPriceTypeLabel(value) }}
                </template>
                <template #max_quantity="{ value }">
                    {{ value === 0 ? 'Neomezeně' : value }}
                </template>
                <template #is_active="{ value }">
                    <Badge :variant="value ? 'default' : 'secondary'">
                        {{ value ? 'Aktivní' : 'Neaktivní' }}
                    </Badge>
                </template>
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button size="sm" variant="outline" @click="startEditing(item)">
                            <Pencil class="h-3 w-3" />
                        </Button>
                        <Button size="sm" variant="destructive" @click="deleteService(item.id)">
                            <Trash2 class="h-3 w-3" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>
        </div>
    </AppLayout>
</template>
