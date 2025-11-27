<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    CalendarRange, 
    ConciergeBell, 
    Mail, 
    CalendarDays,
    ChevronLeft
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

declare const route: any;

const props = defineProps<{
    property: {
        id: number;
        name: string;
        address?: string;
    };
}>();

const page = usePage();

const items = [
    {
        title: 'Přehled',
        href: route('admin.properties.edit', props.property.id),
        icon: LayoutDashboard,
        active: route().current('admin.properties.edit'),
    },
    {
        title: 'Rezervace',
        // Assuming this route exists based on folder structure, falling back to edit if not
        href: route().has('admin.properties.bookings.index') 
            ? route('admin.properties.bookings.index', props.property.id) 
            : '#', 
        icon: CalendarDays,
        active: route().current('admin.properties.bookings.*'),
        disabled: !route().has('admin.properties.bookings.index'),
    },
    {
        title: 'Služby',
        href: route('admin.properties.services.index', props.property.id),
        icon: ConciergeBell,
        active: route().current('admin.properties.services.*'),
    },
    {
        title: 'Sezóny',
        href: route('admin.properties.seasons.index', props.property.id),
        icon: CalendarRange,
        active: route().current('admin.properties.seasons.*'),
    },
    {
        title: 'Emailové šablony',
        href: route('admin.properties.email-templates.index', props.property.id),
        icon: Mail,
        active: route().current('admin.properties.email-templates.*'),
    },
];
</script>

<template>
    <div class="flex h-full flex-col border-r bg-background/50">
        <div class="flex h-14 items-center border-b px-4 lg:h-[60px]">
            <Button variant="ghost" size="icon" as-child class="-ml-2 mr-2 h-8 w-8 text-muted-foreground hover:text-foreground">
                <Link :href="route('admin.properties.index')">
                    <ChevronLeft class="h-4 w-4" />
                </Link>
            </Button>
            <span class="font-semibold tracking-tight truncate flex-1" :title="property.name">
                {{ property.name }}
            </span>
            <slot name="actions" />
        </div>
        
        <div class="flex-1 overflow-auto py-4">
            <nav class="grid items-start px-2 text-sm font-medium lg:px-4 space-y-1">
                <template v-for="item in items" :key="item.title">
                    <Link
                        v-if="!item.disabled"
                        :href="item.href"
                        :class="cn(
                            'flex items-center gap-3 rounded-md px-3 py-2 transition-all hover:text-primary',
                            item.active 
                                ? 'bg-muted text-primary' 
                                : 'text-muted-foreground hover:bg-muted/50'
                        )"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.title }}
                    </Link>
                    <span
                        v-else
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-muted-foreground/50 cursor-not-allowed"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.title }}
                    </span>
                </template>
            </nav>
        </div>
        
        <!-- Bottom section if needed -->
        <div class="mt-auto p-4 border-t">
            <div class="text-xs text-muted-foreground">
                <p class="font-medium text-foreground mb-1">Potřebujete pomoc?</p>
                <p>Kontaktujte podporu pro nastavení nemovitosti.</p>
            </div>
        </div>
    </div>
</template>
