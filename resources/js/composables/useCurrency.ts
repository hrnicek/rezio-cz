import { MoneyData } from "@/types";

export function useCurrency() {
    /**
     * Format cents (integer) or MoneyData to formatted currency string (e.g. "1 200 Kč")
     */
    function formatPrice(amount: number | MoneyData | null | undefined): string {
        if (amount === null || amount === undefined) return '';
        
        if (typeof amount === 'object' && 'formatted' in amount) {
            return amount.formatted;
        }

        // Assuming amount is cents if it's a number
        return new Intl.NumberFormat('cs-CZ', {
            style: 'currency',
            currency: 'CZK',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount / 100);
    }

    /**
     * @deprecated Use formatPrice instead. This function assumes input is UNITS (not cents).
     */
    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('cs-CZ', {
            style: 'currency',
            currency: 'CZK',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    }

    return { formatCurrency, formatPrice };
}
