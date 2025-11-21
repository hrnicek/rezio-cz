<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Building2 } from 'lucide-vue-next';

declare const route: any;

const page = usePage();

const properties = computed(() => page.props.auth.properties || []);
const currentProperty = computed(() => page.props.auth.user?.currentProperty);

const switchProperty = (propertyId: any) => {
    if (!propertyId) return;
    
    router.post(
        route('admin.switch-property'),
        { property_id: String(propertyId) },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <div v-if="properties.length > 1" class="w-full">
        <Select
            :model-value="currentProperty?.id?.toString()"
            @update:model-value="switchProperty"
        >
            <SelectTrigger class="w-full">
                <div class="flex items-center gap-2">
                    <Building2 class="h-4 w-4" />
                    <SelectValue :placeholder="currentProperty?.name || 'Select Property'" />
                </div>
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="property in properties"
                    :key="property.id"
                    :value="property.id.toString()"
                >
                    {{ property.name }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
