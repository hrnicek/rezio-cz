<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppDataTable from '@/components/AppDataTable.vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

declare const route: any;

defineProps<{
    properties: {
        data: Array<{
            id: number;
            name: string;
            address: string;
            description: string;
        }>;
        links: any;
        meta: any;
    };
}>();

const deleteProperty = (id: number) => {
    if (confirm('Opravdu chcete smazat tuto nemovitost?')) {
        router.delete(route('admin.properties.destroy', id));
    }
};

const breadcrumbs = [
    {
        title: 'Nemovitosti',
        href: '/properties',
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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Nemovitosti</h2>
                <Button as-child>
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
                    <span class="font-medium">{{ item.name }}</span>
                </template>
                
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" size="icon" as-child>
                            <Link :href="route('admin.properties.edit', item.id)">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button variant="destructive" size="icon" @click="deleteProperty(item.id)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>
        </div>
    </AppLayout>
</template>
