export const BookingStatus = {
    Pending: 'pending',
    Confirmed: 'confirmed',
    Paid: 'paid',
    Cancelled: 'cancelled',
    Completed: 'completed',
    Blocked: 'blocked',
} as const;

export const BookingStatusLabels: Record<string, string> = {
    [BookingStatus.Pending]: 'Čekající',
    [BookingStatus.Confirmed]: 'Potvrzeno',
    [BookingStatus.Paid]: 'Zaplaceno',
    [BookingStatus.Cancelled]: 'Zrušeno',
    [BookingStatus.Completed]: 'Dokončeno',
    [BookingStatus.Blocked]: 'Blokováno',
    // Fallback for unknown
    'unknown': 'Neznámý',
};

export const PaymentStatus = {
    Pending: 'pending',
    Paid: 'paid',
    Refunded: 'refunded',
    Cancelled: 'cancelled',
    Failed: 'failed',
} as const;

export const PaymentStatusLabels: Record<string, string> = {
    [PaymentStatus.Pending]: 'Čekající',
    [PaymentStatus.Paid]: 'Zaplaceno',
    [PaymentStatus.Refunded]: 'Vráceno',
    [PaymentStatus.Cancelled]: 'Zrušeno',
    [PaymentStatus.Failed]: 'Selhalo',
};

export const ServicePriceType = {
    PerPerson: 'per_person',
    PerNight: 'per_night',
    PerDay: 'per_day', // Legacy
    PerStay: 'per_stay',
    Fixed: 'fixed',
    Flat: 'flat', // Legacy
    PerHour: 'per_hour',
} as const;

export const ServicePriceTypeLabels: Record<string, string> = {
    [ServicePriceType.PerPerson]: 'Za osobu',
    [ServicePriceType.PerNight]: 'Za noc',
    [ServicePriceType.PerDay]: 'Za noc',
    [ServicePriceType.PerStay]: 'Za pobyt',
    [ServicePriceType.Fixed]: 'Fixní',
    [ServicePriceType.Flat]: 'Fixní',
    [ServicePriceType.PerHour]: 'Za hodinu',
};
