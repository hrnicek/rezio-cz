<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppDataTable from '@/components/AppDataTable.vue';
import { Plus, Pencil, Trash2, MapPin } from 'lucide-vue-next';
import { ref } from 'vue';
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

// Matches App\Data\Admin\Property\PropertyData
interface PropertyData {
    id: number;
    name: string;
    address: string | null;
    image: string | null;
    description: string | null;
}

defineProps<{
    properties: {
        data: PropertyData[];
        links: any;
        meta: any;
    };
}>();

const propertyToDelete = ref<number | null>(null);
const isDeleteDialogOpen = ref(false);

const confirmDelete = (id: number) => {
    propertyToDelete.value = id;
    isDeleteDialogOpen.value = true;
};

const deleteProperty = () => {
    if (!propertyToDelete.value) return;

    router.delete(route('admin.properties.destroy', propertyToDelete.value), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            propertyToDelete.value = null;
            toast.success('Nemovitost byla úspěšně smazána.');
        },
        onError: () => {
            toast.error('Nepodařilo se smazat nemovitost.');
        }
    });
};

const breadcrumbs = [
    {
        title: 'Nemovitosti',
        href: route('admin.properties.index'),
    },
];

const columns = [
    { key: 'name', label: 'Název', sortable: true },
    { key: 'address', label: 'Adresa', sortable: true },
    { key: 'actions', label: 'Akce', align: 'right' as const },
];
</script>

<template>
    <Head title="Nemovitosti" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Nemovitosti</h2>
                    <p class="text-muted-foreground">Správa vašich ubytovacích zařízení.</p>
                </div>
                <Button as-child class="shadow-sm h-9">
                    <Link :href="route('admin.properties.create')">
                        <Plus class="mr-2 h-4 w-4" /> Přidat ubytování
                    </Link>
                </Button>
            </div>

            <AppDataTable 
                :data="properties" 
                :columns="columns"
                no-results-message="Žádné nemovitosti nenalezeny."
            >
                <template #name="{ item }">
                    <div class="font-medium text-foreground">{{ item.name }}</div>
                    <div v-if="item.description" class="text-xs text-muted-foreground truncate max-w-xs">
                        {{ item.description }}
                    </div>
                </template>
                
                <template #address="{ item }">
                    <div v-if="item.address" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <MapPin class="h-3 w-3" />
                        {{ item.address }}
                    </div>
                    <span v-else class="text-muted-foreground text-xs">-</span>
                </template>
                
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                            <Link :href="route('admin.properties.edit', item.id)">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive" @click="confirmDelete(item.id)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>

            <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Opravdu chcete smazat tuto nemovitost?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Tato akce je nevratná. Veškerá data spojená s touto nemovitostí budou smazána.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="isDeleteDialogOpen = false">Zrušit</AlertDialogCancel>
                        <AlertDialogAction @click="deleteProperty" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                            Smazat
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>
