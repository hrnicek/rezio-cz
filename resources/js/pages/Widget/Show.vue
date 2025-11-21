<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { computed, ref } from 'vue';
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';

const props = defineProps<{
    property: {
        id: number;
        name: string;
        address: string;
        description: string;
        price_per_night: number;
        widget_token: string;
    };
    errors: Record<string, string>;
    flash: {
        success?: string;
    };
}>();

const range = ref({
    start: new Date(),
    end: new Date(new Date().setDate(new Date().getDate() + 1)),
});

const form = useForm({
    start_date: '',
    end_date: '',
    guest_name: '',
    guest_email: '',
    guest_phone: '',
});

const totalPrice = computed(() => {
    if (!range.value.start || !range.value.end) return 0;
    const start = new Date(range.value.start);
    const end = new Date(range.value.end);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays * props.property.price_per_night;
});

const submit = () => {
    form.start_date = range.value.start.toISOString().split('T')[0];
    form.end_date = range.value.end.toISOString().split('T')[0];
    form.post(route('widget.store', props.property.widget_token), {
        onSuccess: () => {
            form.reset();
            range.value = {
                start: new Date(),
                end: new Date(new Date().setDate(new Date().getDate() + 1)),
            };
        },
    });
};
</script>

<template>
    <Head :title="`Book ${property.name}`" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <Card class="w-full max-w-md">
            <CardHeader>
                <CardTitle>{{ property.name }}</CardTitle>
                <CardDescription>{{ property.address }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="flash.success" class="mb-4 rounded bg-green-100 p-3 text-green-700">
                    {{ flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label>Select Dates</Label>
                        <div class="flex justify-center border rounded-md p-2 bg-white">
                            <VCalendar v-model.range="range" :min-date="new Date()" />
                        </div>
                        <div v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="guest_name">Your Name</Label>
                        <Input id="guest_name" v-model="form.guest_name" required />
                        <div v-if="form.errors.guest_name" class="text-sm text-red-500">{{ form.errors.guest_name }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="guest_email">Email</Label>
                        <Input id="guest_email" type="email" v-model="form.guest_email" required />
                        <div v-if="form.errors.guest_email" class="text-sm text-red-500">{{ form.errors.guest_email }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="guest_phone">Phone</Label>
                        <Input id="guest_phone" v-model="form.guest_phone" required />
                        <div v-if="form.errors.guest_phone" class="text-sm text-red-500">{{ form.errors.guest_phone }}</div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total Price</span>
                            <span>${{ totalPrice.toFixed(2) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 text-right">
                            {{ property.price_per_night }} / night
                        </p>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Request Booking
                    </Button>
                </form>
            </CardContent>
            <CardFooter class="justify-center border-t p-4 text-xs text-gray-500">
                Powered by Rezeo
            </CardFooter>
        </Card>
    </div>
</template>
