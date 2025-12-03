<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Trash2, User, Baby, Pencil } from 'lucide-vue-next';

declare const route: any;

const props = defineProps<{
    booking: any;
    property: any;
    guests: any[];
}>();

const editingGuestId = ref<number | null>(null);

const form = useForm({
    first_name: '',
    last_name: '',
    is_adult: true,
    gender: '',
    nationality: '',
    document_type: '',
    document_number: '',
    birth_date: '',
    address: {
        street: '',
        city: '',
        zip: '',
        country: '',
    },
    signature: '',
});

const editGuest = (guest: any) => {
    editingGuestId.value = guest.id;
    form.first_name = guest.first_name;
    form.last_name = guest.last_name;
    form.is_adult = Boolean(guest.is_adult);
    form.gender = guest.gender || '';
    form.nationality = guest.nationality || '';
    form.document_type = guest.document_type || '';
    form.document_number = guest.document_number || '';
    form.birth_date = guest.birth_date || '';
    form.address = {
        street: guest.address?.street || '',
        city: guest.address?.city || '',
        zip: guest.address?.zip || '',
        country: guest.address?.country || '',
    };
    form.signature = guest.signature || '';
};

const cancelEdit = () => {
    editingGuestId.value = null;
    form.reset();
};

const submit = () => {
    if (editingGuestId.value) {
        form.put(route('check-in.guests.update', [props.booking.code, editingGuestId.value]), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    } else {
        form.post(route('check-in.guests.store', props.booking.code), {
            onSuccess: () => {
                form.reset();
            },
        });
    }
};

const deleteGuest = (guestId: number) => {
    if (confirm('Opravdu chcete smazat tuto osobu?')) {
        useForm({}).delete(route('check-in.guests.destroy', [props.booking.code, guestId]));
    }
};

const adults = computed(() => props.guests.filter(g => g.is_adult));
const children = computed(() => props.guests.filter(g => !g.is_adult));
</script>

<template>
    <GuestLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle>{{ editingGuestId ? 'Upravit údaje' : 'Údaje o osobě' }}</CardTitle>
                        <Button v-if="editingGuestId" variant="ghost" size="sm" @click="cancelEdit">
                            Zrušit úpravy
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="first_name">Jméno</Label>
                                    <Input id="first_name" v-model="form.first_name" required />
                                </div>
                                <div class="space-y-2">
                                    <Label for="last_name">Příjmení</Label>
                                    <Input id="last_name" v-model="form.last_name" required />
                                </div>
                                <div class="space-y-2">
                                    <Label for="gender">Pohlaví</Label>
                                    <Select v-model="form.gender">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Vyberte pohlaví" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="male">Muž</SelectItem>
                                            <SelectItem value="female">Žena</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="nationality">Státní příslušnost</Label>
                                    <Input id="nationality" v-model="form.nationality" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="document_type">Druh dokladu</Label>
                                    <Select v-model="form.document_type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Vyberte druh dokladu" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="passport">Cestovní pas</SelectItem>
                                            <SelectItem value="id_card">Občanský průkaz</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="document_number">Číslo dokladu</Label>
                                    <Input id="document_number" v-model="form.document_number" />
                                </div>
                            </div>

                            <Separator />

                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Narození</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="birth_date">Datum narození</Label>
                                        <Input type="date" id="birth_date" v-model="form.birth_date" />
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Trvalý pobyt</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="space-y-2 md:col-span-2">
                                        <Label for="street">Ulice a číslo popisné</Label>
                                        <Input id="street" v-model="form.address.street" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="city">Město</Label>
                                        <Input id="city" v-model="form.address.city" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="zip">PSČ</Label>
                                        <Input id="zip" v-model="form.address.zip" />
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <Label for="country">Země</Label>
                                        <Input id="country" v-model="form.address.country" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="is_adult" v-model="form.is_adult" class="rounded border-gray-300 text-primary focus:ring-primary" />
                                <Label for="is_adult">Osoba je dospělá (18+)</Label>
                            </div>

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Ukládám...' : (editingGuestId ? 'Aktualizovat údaje' : 'Uložit a přidat další osobu') }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar Section -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Vyplněno ({{ guests.length }})</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div v-if="adults.length > 0">
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">Dospělí ({{ adults.length }})</h4>
                            <div class="space-y-2">
                                <div v-for="guest in adults" :key="guest.id" class="flex items-center justify-between p-3 bg-muted rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <User class="w-4 h-4 text-muted-foreground" />
                                        <span class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</span>
                                    </div>
                                    <div class="flex space-x-1">
                                        <Button variant="ghost" size="icon" @click="editGuest(guest)">
                                            <Pencil class="w-4 h-4 text-muted-foreground" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteGuest(guest.id)">
                                            <Trash2 class="w-4 h-4 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="children.length > 0">
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">Děti ({{ children.length }})</h4>
                            <div class="space-y-2">
                                <div v-for="guest in children" :key="guest.id" class="flex items-center justify-between p-3 bg-muted rounded-md">
                                    <div class="flex items-center space-x-3">
                                        <Baby class="w-4 h-4 text-muted-foreground" />
                                        <span class="font-medium">{{ guest.first_name }} {{ guest.last_name }}</span>
                                    </div>
                                    <div class="flex space-x-1">
                                        <Button variant="ghost" size="icon" @click="editGuest(guest)">
                                            <Pencil class="w-4 h-4 text-muted-foreground" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteGuest(guest.id)">
                                            <Trash2 class="w-4 h-4 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="guests.length === 0" class="text-center text-muted-foreground py-4">
                            Zatím nebyli přidáni žádní hosté.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </GuestLayout>
</template>
