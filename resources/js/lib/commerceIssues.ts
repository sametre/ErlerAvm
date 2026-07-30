export type CommerceIssueSeverity = 'info' | 'warning' | 'error' | 'critical';

export type CommerceIssueCode =
    | 'payment_failed'
    | 'payment_timeout'
    | 'order_creation_failed'
    | 'insufficient_stock'
    | 'price_changed'
    | 'coupon_invalid'
    | 'shipping_region_unsupported'
    | 'product_unavailable'
    | 'account_suspended'
    | 'external_api_error';

export type CommerceIssue = {
    code: CommerceIssueCode;
    title: string;
    description: string;
    severity: CommerceIssueSeverity;
    primaryActionLabel: string;
    secondaryActionLabel?: string;
    supportRequired?: boolean;
};

export const commerceIssues: Record<CommerceIssueCode, CommerceIssue> = {
    payment_failed: {
        code: 'payment_failed',
        title: 'Ödeme tamamlanamadı',
        description:
            'Kart reddi, 3D Secure iptali veya banka taraflı bir hata nedeniyle ödeme alınamadı. Kart bilgilerinizi kontrol edip tekrar deneyebilirsiniz.',
        severity: 'error',
        primaryActionLabel: 'Tekrar dene',
        secondaryActionLabel: 'Sepete dön',
    },
    payment_timeout: {
        code: 'payment_timeout',
        title: 'Ödeme zaman aşımına uğradı',
        description:
            'Ödeme sağlayıcısından beklenen sürede yanıt alınamadı. Kartınızdan tahsilat yapıldıysa sipariş durumu arka planda kontrol edilir.',
        severity: 'warning',
        primaryActionLabel: 'Ödeme adımına dön',
        secondaryActionLabel: 'Sepeti kontrol et',
    },
    order_creation_failed: {
        code: 'order_creation_failed',
        title: 'Sipariş kaydı oluşturulamadı',
        description:
            'Ödeme başarılı olabilir ancak sipariş kaydı tamamlanamadı. Bu kritik durum destek ekibine loglanmalı ve kullanıcıdan tekrar ödeme alınmamalıdır.',
        severity: 'critical',
        primaryActionLabel: 'Destek talebi oluştur',
        supportRequired: true,
    },
    insufficient_stock: {
        code: 'insufficient_stock',
        title: 'Stok yetersiz',
        description:
            'Sepetinizdeki bazı ürünlerin stoğu ödeme öncesinde tükendi veya adet değişti. Sepet güncellendikten sonra ödeme yapılabilir.',
        severity: 'warning',
        primaryActionLabel: 'Sepeti güncelle',
    },
    price_changed: {
        code: 'price_changed',
        title: 'Fiyat değişti',
        description:
            'Sepetinizdeki bazı ürünlerin fiyatı güncellendi. Ödeme öncesinde yeni toplamı onaylamanız gerekir.',
        severity: 'warning',
        primaryActionLabel: 'Güncel fiyatı onayla',
        secondaryActionLabel: 'Sepete dön',
    },
    coupon_invalid: {
        code: 'coupon_invalid',
        title: 'Kupon geçersiz',
        description:
            'Kupon süresi dolmuş, kullanım limiti bitmiş veya sepetiniz minimum tutarı karşılamıyor olabilir.',
        severity: 'info',
        primaryActionLabel: 'Kuponu kaldır',
    },
    shipping_region_unsupported: {
        code: 'shipping_region_unsupported',
        title: 'Kargo bölgesi desteklenmiyor',
        description:
            'Seçtiğiniz teslimat adresine şu anda kargo hizmeti verilemiyor. Farklı bir adres veya teslimat yöntemi seçin.',
        severity: 'warning',
        primaryActionLabel: 'Adresi değiştir',
    },
    product_unavailable: {
        code: 'product_unavailable',
        title: 'Ürün yayında değil',
        description:
            'Bu ürün artık satışta olmayabilir veya geçici olarak pasife alınmış olabilir. Benzer ürünlere yönlendirme yapılmalıdır.',
        severity: 'info',
        primaryActionLabel: 'Benzer ürünleri gör',
    },
    account_suspended: {
        code: 'account_suspended',
        title: 'Hesap kısıtlandı',
        description:
            'Hesabınız güvenlik nedeniyle geçici olarak kısıtlandı. İşlemlerinize devam etmek için destek ekibiyle iletişime geçin.',
        severity: 'critical',
        primaryActionLabel: 'Destek ile iletişime geç',
        supportRequired: true,
    },
    external_api_error: {
        code: 'external_api_error',
        title: 'Dış servis bağlantı hatası',
        description:
            'Ödeme, kargo, fatura veya marketplace servisinden geçici yanıt alınamadı. İşlem kuyruğa alınmalı ve tekrar denenmelidir.',
        severity: 'error',
        primaryActionLabel: 'Tekrar dene',
        supportRequired: true,
    },
};
