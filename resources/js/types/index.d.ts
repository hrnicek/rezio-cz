import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
    properties?: Property[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at?: string;
    two_factor_enabled?: boolean;
    current_property_id?: number;
    currentProperty?: Property;
}

export interface Property {
    id: number;
    name: string;
    slug: string;
}

export interface MoneyData {
    amount: number;
    currency: string;
    value: number;
    formatted: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: Auth;
    name: string;
    quote: { message: string; author: string };
    sidebarOpen: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;
