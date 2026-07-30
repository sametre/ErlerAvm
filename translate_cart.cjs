const fs = require('fs');

const files = [
    'resources/js/Pages/Storefront/Cart.vue',
    'resources/js/components/storefront/CartDrawer.vue'
];

files.forEach(file => {
    if(fs.existsSync(file)) {
        let content = fs.readFileSync(file, 'utf8');
        content = content.replace(/>Sepetiniz Boş</g, ">{{ $t('cart.empty') }}<");
        content = content.replace(/>Sepetinizde ürün bulunmuyor.</g, ">{{ $t('cart.empty_desc') }}<");
        content = content.replace(/>Alışverişi Tamamla</g, ">{{ $t('cart.checkout') }}<");
        content = content.replace(/>Alışverişe Devam Et</g, ">{{ $t('cart.continue_shopping') }}<");
        content = content.replace(/>Ara Toplam</g, ">{{ $t('cart.total') }}<");
        content = content.replace(/>Sepete Git</g, ">{{ $t('nav.cart') }}<");
        fs.writeFileSync(file, content);
    }
});
