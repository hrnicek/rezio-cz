<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import AppDataTable from '@/components/AppDataTable.vue';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
    };
    seasons: {
        data: Array<{
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
        links: any[];
        meta: any;
    };
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
        label: 'Priorita',
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
        key: 'check_in_days',
        label: 'Dny příjezdu'
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

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-9 w-9">
                        <Link :href="route('admin.properties.edit', property.id)">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h2 class="text-2xl font-bold tracking-tight">Sezóny - {{ property.name }}</h2>
                </div>
                <Button @click="startAdding" v-if="!isAdding && !editingId" class="h-9">
                    <Plus class="mr-2 h-4 w-4" />
                    Přidat sezónu
                </Button>
            </div>

            <!-- Add/Edit Form -->
            <Card v-if="isAdding || editingId" class="mb-6">
                <CardHeader>
                    <CardTitle>{{ editingId ? 'Upravit sezónu' : 'Přidat novou sezónu' }}</CardTitle>
                    <CardDescription>
                        {{ editingId ? 'Úprava detailů sezóny' : 'Vytvoření nové cenové sezóny' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Název</Label>
                                <Input id="name" v-model="form.name" placeholder="např. Léto 2025" required class="h-9" />
                                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Cena za noc (Kč)</Label>
                                <Input id="price" v-model.number="form.price" type="number" step="0.01" required class="h-9" />
                                <div v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date">Začátek</Label>
                                <Input id="start_date" v-model="form.start_date" type="date" :required="!form.is_default" :disabled="form.is_default" class="h-9" />
                                <div v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">Konec</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" :required="!form.is_default" :disabled="form.is_default" class="h-9" />
                                <div v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="priority">Priorita</Label>
                                <Input id="priority" v-model.number="form.priority" type="number" class="h-9" />
                                <div v-if="form.errors.priority" class="text-sm text-red-500">{{ form.errors.priority }}</div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="min_stay">Minimální délka pobytu (noci)</Label>
                                <Input id="min_stay" v-model.number="form.min_stay" type="number" min="1" class="h-9" />
                                <div v-if="form.errors.min_stay" class="text-sm text-red-500">{{ form.errors.min_stay }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_default" v-model:checked="form.is_default" />
                                <Label for="is_default" class="cursor-pointer">Výchozí sezóna</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_fixed_range" v-model:checked="form.is_fixed_range" />
                                <Label for="is_fixed_range" class="cursor-pointer">Pevný rozsah</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_recurring" v-model:checked="form.is_recurring" />
                                <Label for="is_recurring" class="cursor-pointer">Opakovaná (roční)</Label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Dny příjezdu (volitelné)</Label>
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
                            <Button type="button" variant="outline" @click="cancelEdit" class="h-9">
                                Zrušit
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="h-9">
                                {{ editingId ? 'Upravit' : 'Vytvořit' }} sezónu
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Seasons Table -->
            <AppDataTable
                :data="seasons"
                :columns="columns"
                search-placeholder="Hledat sezóny..."
            >
                <template #name="{ value }">
                    <div class="font-medium">{{ value }}</div>
                </template>
                <template #priority="{ value }">
                    {{ value || 0 }}
                </template>
                <template #period="{ item }">
                    <span v-if="item.is_default">N/A</span>
                    <span v-else>{{ item.start_date }} - {{ item.end_date }}</span>
                </template>
                <template #price="{ value }">
                    {{ value }} Kč
                </template>
                <template #min_stay="{ value }">
                    {{ value || 1 }} nocí
                </template>
                <template #check_in_days="{ value }">
                    <span v-if="!value || value.length === 0">Kdykoliv</span>
                    <span v-else>{{ value.map((d: number) => weekDays[d].label.substring(0, 2)).join(', ') }}</span>
                </template>
                <template #status="{ item }">
                    <div class="flex gap-1 flex-wrap">
                        <Badge v-if="item.is_default" variant="default">Výchozí</Badge>
                        <Badge v-if="item.is_fixed_range" variant="secondary">Pevný</Badge>
                        <Badge v-if="item.is_recurring" variant="outline">Opakovaná</Badge>
                    </div>
                </template>
                <template #actions="{ item }">
                    <div class="flex justify-end gap-2">
                        <Button size="sm" variant="outline" @click="startEditing(item)" class="h-8 w-8 p-0">
                            <Pencil class="h-3 w-3" />
                        </Button>
                        <Button size="sm" variant="destructive" @click="deleteSeason(item.id)" :disabled="item.is_default" class="h-8 w-8 p-0">
                            <Trash2 class="h-3 w-3" />
                        </Button>
                    </div>
                </template>
            </AppDataTable>
        </div>
    </AppLayout>
</template>
