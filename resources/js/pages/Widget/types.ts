import type { Ref, ComputedRef } from "vue";

export interface Customer {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    note: string;
}

export interface ExtraService {
    id: number;
    name: string;
    price: number;
    price_type: 'per_day' | 'per_stay' | 'per_person' | string;
    description?: string;
}

export interface CalendarService {
    month: Ref<number>;
    year: Ref<number>;
    loading: Ref<boolean>;
    monthLabel: ComputedRef<string>;
    cells: ComputedRef<any[]>;
    daysData: Ref<any[]>;
    changeMonth: (delta: number) => void;
    fetchCalendarData: () => Promise<void>;
    getDayInfo: (dateStr: string) => any;
    parseISO: (s: string) => Date;
    formatDate: (iso: string | null) => string;
    toISO: (d: Date) => string;
}

export interface Property {
    id: number;
    name?: string;
    [key: string]: any;
}
