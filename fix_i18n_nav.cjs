const fs = require('fs');
let content = fs.readFileSync('resources/js/i18n.ts', 'utf8');

// Match 'messages = { ... }' logic
// But it's easier to just do regex replacement inside the nav object for each language.

const replacements = [
  {
    lang: '"tr"',
    search: '"favorites": "Favorilerim"',
    replace: '"favorites": "Favorilerim", "orders": "Siparişlerim", "addresses": "Adreslerim", "notifications": "Bildirimler"'
  },
  {
    lang: '"en"',
    search: '"favorites": "Favorites"',
    replace: '"favorites": "Favorites", "orders": "Orders", "addresses": "Addresses", "notifications": "Notifications"'
  },
  {
    lang: '"ru"',
    search: '"favorites": "Избранное"',
    replace: '"favorites": "Избранное", "orders": "Заказы", "addresses": "Адреса", "notifications": "Уведомления"'
  },
  {
    lang: '"pt"',
    search: '"favorites": "Favoritos"',
    replace: '"favorites": "Favoritos", "orders": "Pedidos", "addresses": "Endereços", "notifications": "Notificações"'
  },
  {
    lang: '"es"',
    search: '"favorites": "Favoritos"',
    replace: '"favorites": "Favoritos", "orders": "Pedidos", "addresses": "Direcciones", "notifications": "Notificaciones"'
  },
  {
    lang: '"ar"',
    search: '"favorites": "المفضلة"',
    replace: '"favorites": "المفضلة", "orders": "طلبات", "addresses": "عناوين", "notifications": "إشعارات"'
  }
];

replacements.forEach(rep => {
  content = content.replace(rep.search, rep.replace);
});

fs.writeFileSync('resources/js/i18n.ts', content);
