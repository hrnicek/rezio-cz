<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    seasons: Array<{
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        price: number;
        min_stay: number;
        check_in_days: number[] | null;
        is_default: boolean;
        is_fixed_range: boolean;
        priority: number;
        is_recurring: boolean;
    }>;
}>();

const editingId = ref<number | null>(null);
const isAdding = ref(false);

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    price: 0,
    min_stay: 1,
    check_in_days: [] as number[],
    is_default: false,
    is_fixed_range: false,
    priority: 0,
    is_recurring: false,
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

const startAdding = () => {
    isAdding.value = true;
    editingId.value = null;
    form.reset();
};

const startEditing = (season: any) => {
    isAdding.value = false;
    editingId.value = season.id;
    form.name = season.name;
    form.start_date = season.start_date;
    form.end_date = season.end_date;
    form.price = season.price;
    form.min_stay = season.min_stay || 1;
    form.check_in_days = season.check_in_days || [];
    form.is_default = season.is_default || false;
    form.is_fixed_range = season.is_fixed_range || false;
    form.priority = season.priority || 0;
    form.is_recurring = season.is_recurring || false;
};

const cancelEdit = () => {
    isAdding.value = false;
    editingId.value = null;
    form.reset();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.properties.seasons.update', [props.property.id, editingId.value]), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    } else {
        form.post(route('admin.properties.seasons.store', props.property.id), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    }
};

const deleteSeason = (seasonId: number) => {
    if (confirm('Opravdu chcete smazat tuto sezónu?')) {
        router.delete(route('admin.properties.seasons.destroy', [props.property.id, seasonId]));
    }
};

const toggleCheckInDay = (day: number) => {
    const index = form.check_in_days.indexOf(day);
    if (index > -1) {
        form.check_in_days.splice(index, 1);
    } else {
        form.check_in_days.push(day);
    }
};

const breadcrumbs = [
    { title: 'Properties', href: '/admin/properties' },
    { title: props.property.name, href: `/admin/properties/${props.property.id}/edit` },
    { title: 'Seasons', href: `/admin/properties/${props.property.id}/seasons` },
];
</script>

<template>
    <Head :title="`Seasons - ${property.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="route('admin.properties.edit', property.id)">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h2 class="text-2xl font-bold tracking-tight">Seasons - {{ property.name }}</h2>
                </div>
                <Button @click="startAdding" v-if="!isAdding && !editingId">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Season
                </Button>
            </div>

            <!-- Add/Edit Form -->
            <Card v-if="isAdding || editingId">
                <CardHeader>
                    <CardTitle>{{ editingId ? 'Edit Season' : 'Add New Season' }}</CardTitle>
                    <CardDescription>
                        {{ editingId ? 'Update season details' : 'Create a new season for pricing' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" placeholder="e.g. Summer 2025" required />
                                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Price per Night (Kč)</Label>
                                <Input id="price" v-model.number="form.price" type="number" step="0.01" required />
                                <div v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" v-model="form.start_date" type="date" :required="!form.is_default" :disabled="form.is_default" />
                                <div v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">End Date</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" :required="!form.is_default" :disabled="form.is_default" />
                                <div v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <Input id="priority" v-model.number="form.priority" type="number" />
                                <div v-if="form.errors.priority" class="text-sm text-red-500">{{ form.errors.priority }}</div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="min_stay">Minimum Stay (nights)</Label>
                                <Input id="min_stay" v-model.number="form.min_stay" type="number" min="1" />
                                <div v-if="form.errors.min_stay" class="text-sm text-red-500">{{ form.errors.min_stay }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 pt-2">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_default" v-model:checked="form.is_default" />
                                <Label for="is_default" class="cursor-pointer">Default Season</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_fixed_range" v-model:checked="form.is_fixed_range" />
                                <Label for="is_fixed_range" class="cursor-pointer">Fixed Range</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_recurring" v-model:checked="form.is_recurring" />
                                <Label for="is_recurring" class="cursor-pointer">Recurring (Annual)</Label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Check-in Days (optional)</Label>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="day in weekDays" :key="day.value" class="flex items-center space-x-2">
                                    <Checkbox 
                                        :id="`day-${day.value}`" 
                                        :checked="form.check_in_days.includes(day.value)"
                                        @update:checked="toggleCheckInDay(day.value)"
                                    />
                                    <Label :for="`day-${day.value}`" class="cursor-pointer">{{ day.label }}</Label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="cancelEdit">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ editingId ? 'Update' : 'Create' }} Season
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Seasons Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Existing Seasons</CardTitle>
                    <CardDescription>
                        Manage pricing seasons for this property
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Period</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Min Stay</TableHead>
                                <TableHead>Check-in Days</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="season in seasons" :key="season.id">
                                <TableCell class="font-medium">{{ season.name }}</TableCell>
                                <TableCell>{{ season.priority || 0 }}</TableCell>
                                <TableCell>
                                    <span v-if="season.is_default">N/A</span>
                                    <span v-else>{{ season.start_date }} - {{ season.end_date }}</span>
                                </TableCell>
                                <TableCell>{{ season.price }} Kč</TableCell>
                                <TableCell>{{ season.min_stay || 1 }} nights</TableCell>
                                <TableCell>
                                    <span v-if="!season.check_in_days || season.check_in_days.length === 0">Any day</span>
                                    <span v-else>{{ season.check_in_days.map(d => weekDays[d].label.substring(0, 2)).join(', ') }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-1 flex-wrap">
                                        <Badge v-if="season.is_default" variant="default">Default</Badge>
                                        <Badge v-if="season.is_fixed_range" variant="secondary">Fixed</Badge>
                                        <Badge v-if="season.is_recurring" variant="outline">Recurring</Badge>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button size="sm" variant="outline" @click="startEditing(season)">
                                            <Pencil class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="destructive" @click="deleteSeason(season.id)" :disabled="season.is_default">
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="seasons.length === 0">
                                <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                    No seasons created yet. Click "Add Season" to create one.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
