<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Building, Calendar, Folder, LayoutGrid, BarChart, CogIcon, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import PropertySwitcher from './PropertySwitcher.vue';

declare const route: any;

const mainNavItems: NavItem[] = [
    {
        title: 'Nástěnka',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Rezervace',
        href: route('admin.bookings.index'),
        icon: Calendar,
    },
     {
        title: 'Kalendář',
        href: route('admin.bookings.index'),
        icon: Calendar,
    },
    {
        title: 'Zákazníci',
        href: route('admin.customers.index'),
        icon: Users,
    },
    {
        title: 'Statistiky',
        href: route('admin.reports.index'),
        icon: BarChart,
    },
        {
        title: 'Fakturace',
        href: route('admin.reports.index'),
        icon: BarChart,
    },
    {
        title: 'Nastavení',
        href: route('admin.reports.index'),
        icon: CogIcon,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Objekty',
        href: route('admin.properties.index'),
        icon: Building,
    },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem>
                    <PropertySwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
