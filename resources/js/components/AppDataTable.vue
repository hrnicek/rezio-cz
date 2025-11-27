<script setup lang="ts">
import { computed } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppPagination from '@/components/AppPagination.vue';
import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    align?: 'left' | 'center' | 'right';
}

interface PaginationData {
    data: any[];
    links: any[];
    meta?: any;
    current_page?: number;
    last_page?: number;
}

const props = defineProps<{
    data: any[] | PaginationData; // Can be just array or paginated object
    columns: Column[];
    sortColumn?: string;
    sortDirection?: 'asc' | 'desc';
    noResultsMessage?: string;
}>();

const emit = defineEmits(['sort-change']);

const items = computed(() => {
    if (Array.isArray(props.data)) {
        return props.data;
    }
    return props.data.data;
});

const paginationLinks = computed(() => {
    if (Array.isArray(props.data)) {
        return [];
    }
    return props.data.links || (props.data.meta ? props.data.meta.links : []);
});

const paginationMeta = computed(() => {
    if (Array.isArray(props.data)) {
        return undefined;
    }
    return props.data.meta || props.data; // Sometimes meta is mixed in top level
});

const handleSort = (column: Column) => {
    if (!column.sortable) return;
    
    let newDirection = 'asc';
    if (props.sortColumn === column.key && props.sortDirection === 'asc') {
        newDirection = 'desc';
    }
    
    emit('sort-change', { column: column.key, direction: newDirection });
};
</script>

<template>
    <div class="space-y-4">
        <div class="rounded-md border border-border bg-card overflow-hidden">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead 
                            v-for="col in columns" 
                            :key="col.key" 
                            :class="[
                                col.class, 
                                col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')
                            ]"
                        >
                            <div 
                                v-if="col.sortable" 
                                class="flex items-center cursor-pointer hover:text-foreground transition-colors"
                                :class="{ 'justify-end': col.align === 'right', 'justify-center': col.align === 'center' }"
                                @click="handleSort(col)"
                            >
                                {{ col.label }}
                                <span class="ml-2 h-4 w-4">
                                    <ArrowUp v-if="sortColumn === col.key && sortDirection === 'asc'" class="h-4 w-4" />
                                    <ArrowDown v-else-if="sortColumn === col.key && sortDirection === 'desc'" class="h-4 w-4" />
                                    <ArrowUpDown v-else class="h-4 w-4 opacity-50" />
                                </span>
                            </div>
                            <span v-else>{{ col.label }}</span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="items.length > 0">
                        <TableRow v-for="(item, index) in items" :key="index">
                            <TableCell 
                                v-for="col in columns" 
                                :key="col.key"
                                :class="[
                                    col.class,
                                    col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')
                                ]"
                            >
                                <slot :name="col.key" :item="item" :value="item[col.key]">
                                    {{ item[col.key] }}
                                </slot>
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">
                            {{ noResultsMessage || 'No results.' }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <AppPagination 
            v-if="paginationLinks.length > 0" 
            :links="paginationLinks" 
            :meta="paginationMeta"
        />
    </div>
</template>
