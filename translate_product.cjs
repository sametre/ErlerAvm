const fs = require('fs');
let content = fs.readFileSync('resources/js/Pages/Storefront/ProductShow.vue', 'utf8');

content = content.replace(/>Sepete Ekle</g, ">{{ $t('common.add_to_cart') }}<");
content = content.replace(/>Hemen Al</g, ">{{ $t('common.quick_buy') }}<");
content = content.replace(/>Stokta Yok</g, ">{{ $t('common.out_of_stock') }}<");
content = content.replace(/>Ürün Detayları</g, ">{{ $t('product.details') }}<");
content = content.replace(/>Değerlendirmeler</g, ">{{ $t('product.reviews') }}<");
content = content.replace(/>Benzer Ürünler</g, ">{{ $t('product.related') }}<");

fs.writeFileSync('resources/js/Pages/Storefront/ProductShow.vue', content);
