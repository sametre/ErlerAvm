const fs = require('fs');

let content = fs.readFileSync('resources/js/components/storefront/StorefrontFooter.vue', 'utf8');

// Replace standard texts
content = content.replace(/'Hesabım'/g, "$t('nav.account')");
content = content.replace(/'Siparişlerim'/g, "$t('nav.orders')");
content = content.replace(/'Adreslerim'/g, "$t('nav.addresses')");
content = content.replace(/'Bildirimler'/g, "$t('nav.notifications')");
content = content.replace(/'Favorilerim'/g, "$t('nav.favorites')");
content = content.replace(/'İade ve Değişim'/g, "$t('footer.return')");
content = content.replace(/'Hakkımızda'/g, "$t('footer.about')");
content = content.replace(/'İletişim'/g, "$t('footer.contact')");

// HTML blocks
content = content.replace(/>Hesabım</g, ">{{ $t('nav.account') }}<");
content = content.replace(/>Kurumsal</g, ">{{ $t('footer.corporate') }}<");
content = content.replace(/>Yasal</g, ">{{ $t('footer.legal') }}<");
content = content.replace(/>Mağaza Konumu</g, ">{{ $t('footer.store_location') }}<");
content = content.replace(/>Instagram'da Biz</g, ">{{ $t('footer.instagram') }}<");
content = content.replace(/Tüm hakları saklıdır\. Bu altyapı güvenle korunmaktadır\./g, "{{ $t('footer.copyright') }}");

fs.writeFileSync('resources/js/components/storefront/StorefrontFooter.vue', content);
