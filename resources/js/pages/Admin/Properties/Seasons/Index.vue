<script setup lang="ts">
import PropertyLayout from '../Partials/PropertyLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { MoneyInput } from '@/components/ui/money-input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import AppDataTable from '@/components/AppDataTable.vue';
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

declare const route: any;

// Interface matching Season model
interface MoneyData {
    amount: number;
    currency: string;
    value: number;
    formatted: string;
}

interface SeasonData {
    id: number;
    name: string;
    start_date: string | null;
    end_date: string | null;
    price_amount: MoneyData;
    min_stay: number | null;
    min_persons: number | null;
    is_default: boolean;
    priority: number;
    is_recurring: boolean;
    is_full_season_booking_only: boolean;
}

interface PropertyData {
    id: number;
    name: string;
    address?: string;
}

const props = defineProps<{
    property: PropertyData;
    seasons: {
        data: SeasonData[];
        links: any[];
        meta: any;
    };
}>();

const isDialogOpen = ref(false);
const editingSeason = ref<SeasonData | null>(null);
const isDeleteDialogOpen = ref(false);
const seasonToDelete = ref<number | null>(null);

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    price: 0, // Maps to price_amount in backend logic, controller handles mapping? Check controller.
    // The controller validates 'price' but model has 'price_amount'. 
    // Assuming controller maps request 'price' to model 'price_amount'.
    // Wait, Season model has 'price_amount' in fillable, but check if there's a mutator or if controller handles it.
    // Controller validates 'price'.
    min_stay: 1,
    min_persons: 1,
    is_default: false,
    priority: 0,
    is_recurring: false,
    is_full_season_booking_only: false,
});

const weekDays = [
    { value: 0, label: 'Neděle' },
    { value: 1, label: 'Pondělí' },
    { value: 2, label: 'Úterý' },
    { value: 3, label: 'Středa' },
    { value: 4, label: 'Čtvrtek' },
    { value: 5, label: 'Pátek' },
    { value: 6, label: 'Sobota' },
];

const openCreateDialog = () => {
    editingSeason.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (season: SeasonData) => {
    editingSeason.value = season;
    form.name = season.name;
    form.start_date = season.start_date ? new Date(season.start_date).toISOString().split('T')[0] : '';
    form.end_date = season.end_date ? new Date(season.end_date).toISOString().split('T')[0] : '';
    form.price = season.price_amount.value; // Using price_amount from model
    form.min_stay = season.min_stay || 1;
    form.min_persons = season.min_persons || 1;
    form.is_default = season.is_default;
    form.priority = season.priority;
    form.is_recurring = season.is_recurring;
    form.is_full_season_booking_only = season.is_full_season_booking_only;
    isDialogOpen.value = true;
};

const closeDialog = () => {
    isDialogOpen.value = false;
    editingSeason.value = null;
    form.reset();
};

const submit = () => {
    if (editingSeason.value) {
        form.put(route('admin.properties.seasons.update', [props.property.id, editingSeason.value.id]), {
            onSuccess: () => {
                closeDialog();
                toast.success('Sezóna byla úspěšně upravena.');
            },
            onError: () => {
                toast.error('Nepodařilo se upravit sezónu. Zkontrolujte formulář.');
            }
        });
    } else {
        form.post(route('admin.properties.seasons.store', props.property.id), {
            onSuccess: () => {
                closeDialog();
                toast.success('Sezóna byla úspěšně vytvořena.');
            },
            onError: () => {
                toast.error('Nepodařilo se vytvořit sezónu. Zkontrolujte formulář.');
            }
        });
    }
};

const confirmDelete = (seasonId: number) => {
    seasonToDelete.value = seasonId;
    isDeleteDialogOpen.value = true;
};

const deleteSeason = () => {
    if (!seasonToDelete.value) return;
    
    router.delete(route('admin.properties.seasons.destroy', [props.property.id, seasonToDelete.value]), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            seasonToDelete.value = null;
            toast.success('Sezóna byla úspěšně smazána.');
        },
        onError: () => {
            toast.error('Nepodařilo se smazat sezónu.');
        }
    });
};

const breadcrumbs = [
    { title: 'Nemovitosti', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Sezóny', href: `/admin/properties/${props.property.id}/seasons` },
];

const columns = [
    {
        key: 'name',
        label: 'Název',
        sortable: true
    },
    {
        key: 'priority',
        label: 'Váha',
        sortable: true
    },
    {
        key: 'period',
        label: 'Období'
    },
    {
        key: 'price',
        label: 'Cena',
        sortable: true
    },
    {
        key: 'min_stay',
        label: 'Min. pobyt',
        sortable: true
    },
    {
        key: 'status',
        label: 'Stav'
    },
    {
        key: 'actions',
        label: 'Akce',
        align: 'right' as const
    }
];
</script>

<template>
    <Head :title="`Sezóny - ${property.name}`" />

    <PropertyLayout :property="property" :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Sezóny</h2>
                    <p class="text-muted-foreground">Správa cenových sezón a omezení.</p>
                </div>
                <Button @click="openCreateDialog" class="h-9 shadow-sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Přidat sezónu
                </Button>
            </div>

            <!-- Seasons Table -->
            <AppDataTable
                :data="seasons"
                :columns="columns"
                search-placeholder="Hledat sezóny..."
            >
                <template #name="{ value }">
                    <div class="font-medium text-foreground">{{ value }}</div>
                </template>
                <template #priority="{ value }">
                    <span class="text-muted-foreground">{{ value || 0 }}</span>
                </template>
                <template #period="{ item }">
                    <span v-if="item.is_default" class="text-muted-foreground text-xs uppercase">Vždy</span>
                    <span v-else class="font-mono text-xs">
                        {{ item.start_date ? new Date(item.start_date).toLocaleDateString('cs-CZ') : '' }} -
                        {{ item.end_date ? new Date(item.end_date).toLocaleDateString('cs-CZ') : '' }}
                    </span>
                </template>
                <template #price="{ item }">
                    <span class="font-medium">{{ item.price_amount.formatted }}</span>
                </template>
                <template #min_stay="{ value }">
                    {{ value || 1 }} <span class="text-muted-foreground text-xs">nocí</span>
                </template>
                <template #status="{ item }">
                    <div class="flex gap-1 flex-wrap">
                        <Badge v-if="item.is_default" variant="secondary" class="rounded-sm shadow-none">Výchozí</Badge>
                        <Badge v-if="item.is_recurring" variant="outline" class="rounded-sm">Opakovaná</Badge>
                    </div>
                </template>
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button size="icon-sm" variant="ghost" @click="openEditDialog(item)" class="h-8 w-8 hover:bg-muted">
                            <Pencil class="h-3 w-3 text-muted-foreground" />
                        </Button>
                        <Button size="icon-sm" variant="ghost" @click="confirmDelete(item.id)" :disabled="item.is_default" class="h-8 w-8 hover:bg-destructive/10 hover:text-destructive">
                            <Trash2 class="h-3 w-3 text-muted-foreground group-hover:text-destructive" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>

            <!-- Create/Edit Dialog -->
            <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                <DialogContent class="sm:max-w-[800px]">
                    <DialogHeader>
                        <DialogTitle>{{ editingSeason ? 'Upravit sezónu' : 'Přidat novou sezónu' }}</DialogTitle>
                        <DialogDescription>
                            {{ editingSeason ? 'Úprava detailů sezóny' : 'Vytvoření nové cenové sezóny' }}
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form @submit.prevent="submit" class="space-y-4 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name" class="text-xs uppercase text-muted-foreground font-mono">Název</Label>
                                <Input id="name" v-model="form.name" placeholder="např. Léto 2025" required class="h-9" />
                                <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price" class="text-xs uppercase text-muted-foreground font-mono">Cena za noc</Label>
                                <MoneyInput id="price" v-model="form.price" class="h-9" />
                                <div v-if="form.errors.price" class="text-sm text-destructive">{{ form.errors.price }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date" class="text-xs uppercase text-muted-foreground font-mono">Začátek</Label>
                                <Input id="start_date" v-model="form.start_date" type="date" :required="!form.is_default" :disabled="form.is_default" class="h-9" />
                                <div v-if="form.errors.start_date" class="text-sm text-destructive">{{ form.errors.start_date }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date" class="text-xs uppercase text-muted-foreground font-mono">Konec</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" :required="!form.is_default" :disabled="form.is_default" class="h-9" />
                                <div v-if="form.errors.end_date" class="text-sm text-destructive">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="priority" class="text-xs uppercase text-muted-foreground font-mono">Váha</Label>
                                <Input id="priority" v-model.number="form.priority" type="number" class="h-9" />
                                <div v-if="form.errors.priority" class="text-sm text-destructive">{{ form.errors.priority }}</div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="min_stay" class="text-xs uppercase text-muted-foreground font-mono">Min. délka (noci)</Label>
                                <Input id="min_stay" v-model.number="form.min_stay" type="number" min="1" class="h-9" />
                                <div v-if="form.errors.min_stay" class="text-sm text-destructive">{{ form.errors.min_stay }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="min_persons" class="text-xs uppercase text-muted-foreground font-mono">Min. počet osob</Label>
                                <Input id="min_persons" v-model.number="form.min_persons" type="number" min="1" class="h-9" />
                                <div v-if="form.errors.min_persons" class="text-sm text-destructive">{{ form.errors.min_persons }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_default" v-model:checked="form.is_default" />
                                <Label for="is_default" class="cursor-pointer text-sm font-medium">Výchozí sezóna</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_recurring" v-model:checked="form.is_recurring" />
                                <Label for="is_recurring" class="cursor-pointer text-sm font-medium">Opakovaná (roční)</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_full_season_booking_only" v-model:checked="form.is_full_season_booking_only" />
                                <Label for="is_full_season_booking_only" class="cursor-pointer text-sm font-medium">Pouze celá sezóna</Label>
                            </div>
                        </div>

                        <DialogFooter class="pt-4">
                            <Button type="button" variant="outline" @click="closeDialog" class="h-9">
                                Zrušit
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="h-9">
                                {{ editingSeason ? 'Upravit' : 'Vytvořit' }} sezónu
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Alert Dialog -->
            <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Opravdu chcete smazat tuto sezónu?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Tato akce je nevratná. Sezóna bude trvale odstraněna ze systému.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="isDeleteDialogOpen = false">Zrušit</AlertDialogCancel>
                        <AlertDialogAction @click="deleteSeason" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                            Smazat
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </PropertyLayout>
</template>
