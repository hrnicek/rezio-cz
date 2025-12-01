<script setup lang="ts">
import PropertyLayout from '../Partials/PropertyLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { ServicePriceType, ServicePriceTypeLabels } from '@/lib/enums';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
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
import AppDataTable from '@/components/AppDataTable.vue';

declare const route: any;

const availablePriceTypes = [
    ServicePriceType.PerPerson,
    ServicePriceType.PerNight,
    ServicePriceType.PerDay,
    ServicePriceType.PerStay,
    ServicePriceType.Fixed,
    ServicePriceType.Flat,
    ServicePriceType.PerHour
];

interface ServiceData {
    id: number;
    name: string;
    description: string | null;
    price_type: string;
    price_amount: number;
    max_quantity: number;
    is_active: boolean;
}

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    services: {
        data: ServiceData[];
        links: any[];
        meta: any;
    };
}>();

const isDialogOpen = ref(false);
const editingService = ref<ServiceData | null>(null);
const isDeleteDialogOpen = ref(false);
const serviceToDelete = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
    price_type: ServicePriceType.Fixed,
    price_amount: 0,
    max_quantity: 0,
    is_active: true,
});

const openCreateDialog = () => {
    editingService.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (service: ServiceData) => {
    editingService.value = service;
    form.name = service.name;
    form.description = service.description || '';
    form.price_type = service.price_type as any;
    form.price_amount = service.price_amount;
    form.max_quantity = service.max_quantity;
    form.is_active = !!service.is_active;
    isDialogOpen.value = true;
};

const closeDialog = () => {
    isDialogOpen.value = false;
    editingService.value = null;
    form.reset();
};

const submit = () => {
    if (editingService.value) {
        form.put(route('admin.properties.services.update', [props.property.id, editingService.value.id]), {
            onSuccess: () => {
                closeDialog();
                toast.success('Služba byla úspěšně upravena.');
            },
            onError: () => {
                toast.error('Nepodařilo se upravit službu. Zkontrolujte formulář.');
            }
        });
    } else {
        form.post(route('admin.properties.services.store', props.property.id), {
            onSuccess: () => {
                closeDialog();
                toast.success('Služba byla úspěšně vytvořena.');
            },
            onError: () => {
                toast.error('Nepodařilo se vytvořit službu. Zkontrolujte formulář.');
            }
        });
    }
};

const confirmDelete = (serviceId: number) => {
    serviceToDelete.value = serviceId;
    isDeleteDialogOpen.value = true;
};

const deleteService = () => {
    if (!serviceToDelete.value) return;
    
    router.delete(route('admin.properties.services.destroy', [props.property.id, serviceToDelete.value]), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            serviceToDelete.value = null;
            toast.success('Služba byla úspěšně smazána.');
        },
        onError: () => {
            toast.error('Nepodařilo se smazat službu.');
        }
    });
};

const breadcrumbs = [
    { title: 'Nemovitosti', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Služby', href: `/admin/properties/${props.property.id}/services` },
];

const columns = [
    {
        key: 'name',
        label: 'Název',
        sortable: true
    },
    {
        key: 'price_amount',
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

    <PropertyLayout :property="property" :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Služby</h2>
                    <p class="text-muted-foreground">Správa doplňkových služeb a poplatků.</p>
                </div>
                <Button @click="openCreateDialog" class="h-9 shadow-sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Přidat službu
                </Button>
            </div>

            <!-- Services Table -->
            <AppDataTable
                :data="services"
                :columns="columns"
                search-placeholder="Hledat služby..."
            >
                <template #name="{ item }">
                    <div class="font-medium text-foreground">{{ item.name }}</div>
                    <div v-if="item.description" class="text-xs text-muted-foreground truncate max-w-[200px]">{{ item.description }}</div>
                </template>
                <template #price_amount="{ value }">
                    <span class="font-mono font-medium">{{ value }} Kč</span>
                </template>
                <template #price_type="{ value }">
                    <Badge variant="outline" class="font-normal">
                        {{ ServicePriceTypeLabels[value] || value }}
                    </Badge>
                </template>
                <template #max_quantity="{ value }">
                    <span v-if="value === 0" class="text-muted-foreground text-xs">Neomezeně</span>
                    <span v-else class="font-mono">{{ value }}</span>
                </template>
                <template #is_active="{ value }">
                    <Badge :variant="value ? 'default' : 'secondary'" class="rounded-sm">
                        {{ value ? 'Aktivní' : 'Neaktivní' }}
                    </Badge>
                </template>
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button size="sm" variant="ghost" class="h-8 w-8 p-0" @click="openEditDialog(item)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button size="sm" variant="ghost" class="h-8 w-8 p-0 text-destructive hover:text-destructive" @click="confirmDelete(item.id)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>

            <!-- Create/Edit Dialog -->
            <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                <DialogContent class="sm:max-w-[600px]">
                    <DialogHeader>
                        <DialogTitle>{{ editingService ? 'Upravit službu' : 'Přidat novou službu' }}</DialogTitle>
                        <DialogDescription>
                            {{ editingService ? 'Úprava detailů služby' : 'Vytvoření nové doplňkové služby pro tuto nemovitost' }}
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form @submit.prevent="submit" class="space-y-4 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Název</Label>
                                <Input id="name" v-model="form.name" placeholder="např. Snídaně" required class="h-9" />
                                <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Cena (Kč)</Label>
                                <Input id="price" v-model.number="form.price_amount" type="number" step="0.01" required class="h-9 font-mono" />
                                <div v-if="form.errors.price_amount" class="text-sm text-destructive">{{ form.errors.price_amount }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="price_type">Typ ceny</Label>
                                <Select v-model="form.price_type">
                                    <SelectTrigger class="h-9">
                                        <SelectValue placeholder="Vyberte typ ceny" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in availablePriceTypes" :key="type" :value="type">
                                            {{ ServicePriceTypeLabels[type] }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.price_type" class="text-sm text-destructive">{{ form.errors.price_type }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="max_quantity">Max. množství (0 pro neomezeně)</Label>
                                <Input id="max_quantity" v-model.number="form.max_quantity" type="number" min="0" required class="h-9 font-mono" />
                                <div v-if="form.errors.max_quantity" class="text-sm text-destructive">{{ form.errors.max_quantity }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Popis</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Popis služby..." rows="3" />
                            <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                        </div>

                        <div class="flex items-center space-x-2 pt-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">Aktivní</Label>
                        </div>

                        <DialogFooter class="pt-4">
                            <Button type="button" variant="outline" @click="closeDialog" class="h-9">
                                Zrušit
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="h-9">
                                {{ editingService ? 'Upravit' : 'Vytvořit' }} službu
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Alert Dialog -->
            <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Opravdu chcete smazat tuto službu?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Tato akce je nevratná. Služba bude trvale odstraněna ze systému.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="isDeleteDialogOpen = false">Zrušit</AlertDialogCancel>
                        <AlertDialogAction @click="deleteService" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                            Smazat
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </PropertyLayout>
</template>