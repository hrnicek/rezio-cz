<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ChevronLeft } from 'lucide-vue-next';

declare const route: any;

const form = useForm({
    name: '',
    address: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.properties.store'));
};

const breadcrumbs = [
    {
        title: 'Nemovitosti',
        href: '/admin/properties',
    },
    {
        title: 'Vytvořit',
        href: '/admin/properties/create',
    },
];
</script>

<template>
    <Head title="Vytvořit nemovitost" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('admin.properties.index')">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <h2 class="text-2xl font-bold tracking-tight">Vytvořit nemovitost</h2>
            </div>

            <div class="mx-auto w-full max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Detaily nemovitosti</CardTitle>
                        <CardDescription>
                            Zadejte detaily nové nemovitosti.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="name">Název</Label>
                                <Input id="name" v-model="form.name" placeholder="např. Horská chata" required />
                                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Adresa</Label>
                                <Textarea id="address" v-model="form.address" placeholder="Celá adresa nemovitosti" />
                                <div v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Popis</Label>
                                <Textarea id="description" v-model="form.description" placeholder="Krátký popis pro widget" />
                                <div v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</div>
                            </div>

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="form.processing">
                                    Vytvořit nemovitost
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
