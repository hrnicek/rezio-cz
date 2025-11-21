<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

defineProps<{
    properties: Array<{
        id: number;
        name: string;
        address: string;
        description: string;
        widget_token: string;
    }>;
}>();

const deleteProperty = (id: number) => {
    if (confirm('Are you sure you want to delete this property?')) {
        router.delete(route('properties.destroy', id));
    }
};

const breadcrumbs = [
    {
        title: 'Properties',
        href: '/properties',
    },
];
</script>

<template>
    <Head title="Properties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Properties</h2>
                <Button as-child>
                    <Link :href="route('properties.create')">
                        <Plus class="mr-2 h-4 w-4" /> Add Property
                    </Link>
                </Button>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Address</TableHead>
                            <TableHead>Widget Token</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="property in properties" :key="property.id">
                            <TableCell class="font-medium">{{ property.name }}</TableCell>
                            <TableCell>{{ property.address }}</TableCell>
                            <TableCell>
                                <code class="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold">
                                    {{ property.widget_token }}
                                </code>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="outline" size="icon" as-child>
                                        <Link :href="route('properties.edit', property.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="destructive" size="icon" @click="deleteProperty(property.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="properties.length === 0">
                            <TableCell colspan="4" class="h-24 text-center">
                                No properties found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
