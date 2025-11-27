<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { format } from 'date-fns';

declare const route: any;

defineProps<{
    cleaningTasks: {
        data: Array<{
            id: number;
            booking_id: number;
            property_id: number;
            due_date: string;
            completed_at: string | null;
            notes: string | null;
            created_at: string;
            updated_at: string;
            booking: {
                id: number;
                customer: {
                    first_name: string;
                    last_name: string;
                    email: string;
                } | null;
                property: {
                    id: number;
                    name: string;
                };
            };
            property: {
                id: number;
                name: string;
            };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const markAsComplete = (taskId: number) => {
    router.post(route('admin.cleaning-tasks.complete', taskId), {}, {
        onSuccess: () => {
            // Optional: display a success message
            console.log(`Task ${taskId} marked as complete.`);
        },
        onError: (errors) => {
            // Optional: display error messages
            console.error('Error marking task as complete:', errors);
        },
    });
};
</script>

<template>
    <Head title="Úklid" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                Úklid
            </h2>
        </template>

        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-foreground">Nadcházející úklidy</h2>
            </div>

            <div v-if="cleaningTasks.data.length > 0" class="rounded-md border border-border bg-card overflow-hidden">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                ID Rezervace
                            </th>
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                Nemovitost
                            </th>
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                Host
                            </th>
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                Termín
                            </th>
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                Poznámky
                            </th>
                            <th scope="col" class="h-9 px-4 text-left align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground">
                                Stav
                            </th>
                            <th scope="col" class="h-9 px-4 align-middle font-mono text-xs uppercase tracking-wider text-muted-foreground text-right">
                                Akce
                            </th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="task in cleaningTasks.data" :key="task.id" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <td class="p-2 px-4 align-middle font-medium">
                                {{ task.booking_id }}
                            </td>
                            <td class="p-2 px-4 align-middle">
                                {{ task.property.name }}
                            </td>
                            <td class="p-2 px-4 align-middle">
                                <div v-if="task.booking.customer">
                                    <div class="font-medium">{{ task.booking.customer.first_name }} {{ task.booking.customer.last_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ task.booking.customer.email }}</div>
                                </div>
                                <span v-else class="italic text-muted-foreground">Žádné info o hostovi</span>
                            </td>
                            <td class="p-2 px-4 align-middle">
                                {{ format(new Date(task.due_date), 'dd.MM.yyyy') }}
                            </td>
                            <td class="p-2 px-4 align-middle">
                                {{ task.notes ?? '-' }}
                            </td>
                            <td class="p-2 px-4 align-middle">
                                <span v-if="task.completed_at" class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-green-500 text-white hover:bg-green-500/80">
                                    Hotovo
                                </span>
                                <span v-else class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-yellow-500 text-white hover:bg-yellow-500/80">
                                    Čekající
                                </span>
                            </td>
                            <td class="p-2 px-4 align-middle text-right">
                                <button
                                    v-if="!task.completed_at"
                                    @click="markAsComplete(task.id)"
                                    class="text-sm font-medium text-primary hover:underline"
                                >
                                    Označit jako hotové
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-muted-foreground">
                Nebyly nalezeny žádné úkoly úklidu.
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center" v-if="cleaningTasks.links.length > 3">
                <template v-for="(link, key) in cleaningTasks.links">
                    <div
                        v-if="link.url === null"
                        :key="key"
                        class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-muted-foreground border border-border rounded bg-muted"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        :key="`link-${key}`"
                        class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border border-border rounded hover:bg-accent hover:text-accent-foreground"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        :href="link.url"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
