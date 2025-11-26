import { defineStore } from 'pinia';
import type { Customer, ExtraService } from '@/pages/Widget/types';

export const useBookingStore = defineStore('booking', {
  state: () => ({
    customer: {
      firstName: 'Jakub',
      lastName: 'Novák',
      email: 'jakub@novak.com',
      phone: '+420 731 786 686',
      note: 'test',
    } as Customer,
    startDate: null as string | null,
    endDate: null as string | null,
    extras: [] as ExtraService[],
    extraSelection: {} as Record<number | string, number>,
  }),
  getters: {
    isFormFilled: (state) => {
      return !!(
        state.customer.firstName &&
        state.customer.lastName &&
        state.customer.email &&
        state.customer.phone
      );
    },
  },
  actions: {
    updateCustomer(partial: Partial<Customer>) {
      this.customer = { ...this.customer, ...partial };
    },
    resetCustomer() {
      this.customer = { firstName: '', lastName: '', email: '', phone: '', note: '' };
    },
    setStartDate(date?: string | null) {
      this.startDate = date || null;
    },
    setEndDate(date?: string | null) {
      this.endDate = date || null;
    },
    resetDates() {
      this.startDate = null;
      this.endDate = null;
    },
    setExtras(list: ExtraService[]) {
      this.extras = Array.isArray(list) ? list : [];
    },
    setExtraQuantity(id: number | string, qty: number) {
      const num = Number(qty);
      const clean = Number.isNaN(num) || num < 0 ? 0 : Math.floor(num);
      this.extraSelection = { ...this.extraSelection, [id]: clean };
    },
    resetExtras() {
      this.extras = [];
      this.extraSelection = {};
    },
  },
});
