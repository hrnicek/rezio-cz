<script setup lang="ts">
import PropertyLayout from './Partials/PropertyLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
        address: string;
        description: string;
    };
}>();

const form = useForm({
    name: props.property.name,
    address: props.property.address,
    description: props.property.description,
});

const submit = () => {
    form.put(route('admin.properties.update', props.property.id));
};

const breadcrumbs = [
    {
        title: 'Nemovitosti',
        href: '/admin/properties',
    },
    {
        title: props.property.name,
        href: `/admin/properties/${props.property.id}/edit`,
    },
    {
        title: 'Přehled',
        href: `/admin/properties/${props.property.id}/edit`,
    },
];
</script>

<template>
    <Head title="Upravit nemovitost" />

    <PropertyLayout :property="property" :breadcrumbs="breadcrumbs">
        <div class="space-y-6 max-w-4xl">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Přehled nemovitosti</h2>
                <p class="text-muted-foreground">Základní informace a nastavení.</p>
            </div>

            <Card class="border shadow-none">
                <CardHeader>
                    <CardTitle>Detaily nemovitosti</CardTitle>
                    <CardDescription>
                        Informace zobrazované na webu a v rezervacích.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Název nemovitosti</Label>
                            <Input id="name" v-model="form.name" placeholder="např. Horská chata" required class="h-9" />
                            <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="address">Adresa</Label>
                            <Textarea id="address" v-model="form.address" placeholder="Celá adresa nemovitosti" class="min-h-[80px]" />
                            <div v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Popis</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Krátký popis pro widget" class="min-h-[120px]" />
                            <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <Button type="submit" :disabled="form.processing" class="h-9 shadow-sm">
                                Uložit změny
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </PropertyLayout>
</template>
