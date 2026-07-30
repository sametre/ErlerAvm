const fs = require('fs');

let content = fs.readFileSync('resources/js/Pages/Storefront/Checkout.vue', 'utf8');

content = content.replace(/>Ödeme Adımları</g, ">{{ $t('checkout.title') }}<");
content = content.replace(/>Sipariş Özeti</g, ">{{ $t('checkout.order_summary') }}<");
content = content.replace(/>Ödenecek Tutar</g, ">{{ $t('checkout.total_to_pay') }}<");
content = content.replace(/>Siparişi Onayla</g, ">{{ $t('checkout.place_order') }}<");

fs.writeFileSync('resources/js/Pages/Storefront/Checkout.vue', content);
