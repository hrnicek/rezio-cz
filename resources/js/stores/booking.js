import { defineStore } from "pinia";

export const useBookingStore = defineStore("booking", {
    state: () => ({
        customer: {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            note: "",
        },
        startDate: null,
        endDate: null,
        extras: [],
        extraSelection: {},
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
        updateCustomer(partial) {
            this.customer = { ...this.customer, ...partial };
        },
        resetCustomer() {
            this.customer = { firstName: "", lastName: "", email: "", phone: "", note: "" };
        },
        setStartDate(date) {
            this.startDate = date || null;
        },
        setEndDate(date) {
            this.endDate = date || null;
        },
        resetDates() {
            this.startDate = null;
            this.endDate = null;
        },
        setExtras(list) {
            this.extras = Array.isArray(list) ? list : [];
        },
        setExtraQuantity(id, qty) {
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
