export const orderStatuses: Record<string, string> = {
    pending: 'Beklemede',
    processing: 'Hazırlanıyor',
    shipped: 'Kargoya Verildi',
    delivered: 'Teslim Edildi',
    cancelled: 'İptal Edildi',
    refunded: 'İade Edildi',
};

export const paymentStatuses: Record<string, string> = {
    payment_pending: 'Ödeme Bekleniyor',
    pending: 'Ödeme Bekleniyor',
    paid: 'Ödendi',
    failed: 'Başarısız',
    refunded: 'İade Edildi',
};

export function translateOrderStatus(status: string): string {
    return orderStatuses[status] || status;
}

export function translatePaymentStatus(status: string): string {
    return paymentStatuses[status] || status;
}

export function getOrderStatusColor(status: string): string {
    const colors: Record<string, string> = {
        pending:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500',
        processing:
            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500',
        shipped:
            'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-500',
        delivered:
            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500',
        cancelled:
            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500',
    };
    return (
        colors[status] ||
        'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-400'
    );
}

export function getPaymentStatusColor(status: string): string {
    const colors: Record<string, string> = {
        payment_pending:
            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-500',
        pending:
            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-500',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500',
    };
    return (
        colors[status] ||
        'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-400'
    );
}
