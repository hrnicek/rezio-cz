<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { ref } from 'vue';

const props = defineProps<{
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
                guest_info: {
                    name: string;
                    email: string;
                };
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
    <Head title="Cleaning Tasks" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cleaning Tasks
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Upcoming Cleaning Tasks</h3>

                        <div v-if="cleaningTasks.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Booking ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Property
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Guest
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Notes
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="task in cleaningTasks.data" :key="task.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ task.booking_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ task.property.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ task.booking.guest_info.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ format(new Date(task.due_date), 'MMM dd, yyyy') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ task.notes ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            <span v-if="task.completed_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                Completed
                                            </span>
                                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                v-if="!task.completed_at"
                                                @click="markAsComplete(task.id)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600"
                                            >
                                                Mark as Complete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-gray-500 dark:text-gray-400">
                            No cleaning tasks found.
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center" v-if="cleaningTasks.links.length > 3">
                            <template v-for="(link, key) in cleaningTasks.links">
                                <div
                                    v-if="link.url === null"
                                    :key="key"
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded dark:text-gray-600"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    :key="`link-${key}`"
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500 dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:border-indigo-600 dark:focus:text-indigo-400"
                                    :class="{ 'bg-white dark:bg-gray-700': link.active }"
                                    :href="link.url"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
