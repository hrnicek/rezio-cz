<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Plus, Search, Download } from 'lucide-vue-next'
import { useDebounceFn } from '@vueuse/core'

declare const route: any;

const props = defineProps<{
    invoices: {
        data: any[];
        links: any;
        meta: any;
    };
    filters?: {
        search?: string;
    };
}>()

const search = ref(props.filters?.search || '')

const breadcrumbs = [
    { title: 'Faktury', href: route('admin.invoices.index') },
]

const columns = [
    { key: 'number', label: 'Doklad' },
    { key: 'type', label: 'Typ dokladu' },
    { key: 'issued_date', label: 'Datum vyst.' },
    { key: 'due_date', label: 'Datum splat.' },
    { key: 'tax_date', label: 'DUZP' },
    { key: 'customer_name', label: 'Jméno' },
    { key: 'property_name', label: 'Objekt' },
    { key: 'variable_symbol', label: 'VS' },
    { key: 'total_price', label: 'Cena', align: 'right' as const },
    { key: 'actions', label: 'Akce', align: 'right' as const },
]

const handleSearch = useDebounceFn((value: string) => {
    router.get(
        route('admin.invoices.index'),
        { search: value },
        { preserveState: true, replace: true }
    )
}, 300)

watch(search, (value) => {
    handleSearch(value)
})
</script>

<template>
    <Head title="Faktury" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Faktury</h2>
                    <p class="text-muted-foreground">Přehled všech vystavených dokladů.</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative">
                        <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Hledat fakturu..."
                            class="pl-8 w-[250px]"
                        />
                    </div>
                    <Button variant="outline" size="sm">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Nová faktura
                    </Button>
                </div>
            </div>

            <AppDataTable :data="invoices" :columns="columns">
                <template #number="{ item }">
                    <div class="font-medium">{{ item.number }}</div>
                </template>

                <template #type="{ item }">
                    {{ item.type_label }}
                </template>

                <template #total_price="{ item }">
                    <span class="font-bold font-mono">
                        {{ item.total_price.formatted }}
                    </span>
                </template>

                <template #actions="{ item }">
                    <Button variant="ghost" size="sm">Detail</Button>
                </template>
            </AppDataTable>
        </div>
    </AppLayout>
</template>
