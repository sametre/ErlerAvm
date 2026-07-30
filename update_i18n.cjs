const fs = require('fs');

const messages = {
  tr: {
    nav: { home: 'Ana Sayfa', products: 'Ürünler', categories: 'Kategoriler', search: 'Ara', cart: 'Sepet', account: 'Hesabım', login: 'Giriş Yap', menu: 'ErlerAVM Menü', menu_desc: 'Kategori, hesap ve destek bağlantıları.', favorites: 'Favorilerim' },
    common: { search_placeholder: 'Ürün, kategori veya marka ara', search_min_chars: 'Arama yapmak için en az 2 harf girin.', search_not_found: 'Sonuç bulunamadı', search_try_again: 'Başka kelimelerle tekrar deneyin.', search_searching: 'Sizin için aranıyor...', search_suggestions: 'Önerilen Ürünler', all_results: 'Tüm Sonuçları Gör', add_to_cart: 'Sepete Ekle', quick_buy: 'Hızlı Satın Al', out_of_stock: 'Stokta Yok', added: 'Eklendi', loading: 'Yükleniyor...', discount: 'İNDİRİM', filter: 'Filtrele', clear_filters: 'Filtreleri Temizle', categories: 'Kategoriler', brands: 'Markalar', price_range: 'Fiyat Aralığı', in_stock_only: 'Sadece Stoktakiler', min: 'En Az', max: 'En Çok' },
    footer: {
      free_shipping: 'Ücretsiz Kargo', free_shipping_desc: '200 TL üzeri siparişlerde kargo bedava', secure_payment: 'Güvenli Ödeme', secure_payment_desc: '256-bit SSL güvenlikli alışveriş', easy_return: 'Kolay İade', easy_return_desc: '14 gün içinde koşulsuz iade hakkı', support: '7/24 Destek', support_desc: 'Her zaman yanınızdayız', newsletter: 'E-Bülten Kayıt', newsletter_desc: 'Kampanyalardan ilk siz haberdar olun.', shopping: 'Alışveriş', corporate: 'Kurumsal', legal: 'Yasal', store_location: 'Mağaza Konumu', instagram: 'Instagram\'da Biz', copyright: 'Tüm hakları saklıdır.', about: 'Hakkımızda', contact: 'İletişim', faq: 'Sıkça Sorulan Sorular', shipping: 'Kargo Hesaplama', policies: 'Politikalar', terms: 'Kullanım Koşulları', privacy: 'Gizlilik Sözleşmesi', return: 'İade ve Değişim'
    },
    cart: { empty: 'Sepetiniz Boş', empty_desc: 'Sepetinizde henüz ürün bulunmuyor. Ürünlerimizi inceleyerek hemen alışverişe başlayabilirsiniz.', total: 'Ara Toplam', checkout: 'Alışverişi Tamamla', continue_shopping: 'Alışverişe Devam Et', product: 'Ürün', price: 'Fiyat', quantity: 'Adet' },
    checkout: { title: 'Ödeme Adımları', address: 'Adres', payment: 'Ödeme', confirm: 'Onay', order_summary: 'Sipariş Özeti', total_to_pay: 'Ödenecek Tutar', place_order: 'Siparişi Onayla' },
    product: { details: 'Ürün Detayları', reviews: 'Değerlendirmeler', related: 'Benzer Ürünler' }
  },
  en: {
    nav: { home: 'Home', products: 'Products', categories: 'Categories', search: 'Search', cart: 'Cart', account: 'My Account', login: 'Login', menu: 'Menu', menu_desc: 'Categories, account and support links.', favorites: 'Favorites' },
    common: { search_placeholder: 'Search products...', search_min_chars: 'Enter at least 2 characters.', search_not_found: 'No results found', search_try_again: 'Try again with different keywords.', search_searching: 'Searching...', search_suggestions: 'Suggested Products', all_results: 'See All Results', add_to_cart: 'Add to Cart', quick_buy: 'Buy Now', out_of_stock: 'Out of Stock', added: 'Added', loading: 'Loading...', discount: 'SALE', filter: 'Filter', clear_filters: 'Clear Filters', categories: 'Categories', brands: 'Brands', price_range: 'Price Range', in_stock_only: 'In Stock Only', min: 'Min', max: 'Max' },
    footer: {
      free_shipping: 'Free Shipping', free_shipping_desc: 'Free shipping on orders over 200 TL', secure_payment: 'Secure Payment', secure_payment_desc: '256-bit SSL secure shopping', easy_return: 'Easy Returns', easy_return_desc: '14-day unconditional return', support: '24/7 Support', support_desc: 'We are always with you', newsletter: 'Newsletter', newsletter_desc: 'Be the first to know about campaigns.', shopping: 'Shopping', corporate: 'Corporate', legal: 'Legal', store_location: 'Store Location', instagram: 'We on Instagram', copyright: 'All rights reserved.', about: 'About Us', contact: 'Contact', faq: 'FAQ', shipping: 'Shipping', policies: 'Policies', terms: 'Terms of Service', privacy: 'Privacy Policy', return: 'Returns'
    },
    cart: { empty: 'Your Cart is Empty', empty_desc: 'There are no items in your cart yet.', total: 'Subtotal', checkout: 'Checkout', continue_shopping: 'Continue Shopping', product: 'Product', price: 'Price', quantity: 'Quantity' },
    checkout: { title: 'Checkout Steps', address: 'Address', payment: 'Payment', confirm: 'Confirm', order_summary: 'Order Summary', total_to_pay: 'Total to Pay', place_order: 'Place Order' },
    product: { details: 'Product Details', reviews: 'Reviews', related: 'Related Products' }
  },
  ru: {
    nav: { home: 'Главная', products: 'Продукты', categories: 'Категории', search: 'Поиск', cart: 'Корзина', account: 'Аккаунт', login: 'Войти', menu: 'Меню', menu_desc: 'Категории и поддержка.', favorites: 'Избранное' },
    common: { search_placeholder: 'Поиск товаров...', search_min_chars: 'Мин. 2 символа.', search_not_found: 'Не найдено', search_try_again: 'Попробуйте снова.', search_searching: 'Поиск...', search_suggestions: 'Рекомендуемые', all_results: 'Все результаты', add_to_cart: 'В корзину', quick_buy: 'Купить сейчас', out_of_stock: 'Нет в наличии', added: 'Добавлено', loading: 'Загрузка...', discount: 'СКИДКА', filter: 'Фильтр', clear_filters: 'Очистить', categories: 'Категории', brands: 'Бренды', price_range: 'Диапазон цен', in_stock_only: 'В наличии', min: 'Мин', max: 'Макс' },
    footer: {
      free_shipping: 'Бесплатная доставка', free_shipping_desc: 'Для заказов от 200 TL', secure_payment: 'Безопасная оплата', secure_payment_desc: '256-bit SSL', easy_return: 'Простой возврат', easy_return_desc: '14 дней на возврат', support: 'Поддержка 24/7', support_desc: 'Мы всегда рядом', newsletter: 'Рассылка', newsletter_desc: 'Узнавайте о акциях первыми.', shopping: 'Покупки', corporate: 'Корпоративная', legal: 'Правовая информация', store_location: 'Наш магазин', instagram: 'Мы в Instagram', copyright: 'Все права защищены.', about: 'О нас', contact: 'Контакты', faq: 'Частые вопросы', shipping: 'Доставка', policies: 'Политика', terms: 'Условия', privacy: 'Конфиденциальность', return: 'Возвраты'
    },
    cart: { empty: 'Корзина пуста', empty_desc: 'В вашей корзине пока нет товаров.', total: 'Итого', checkout: 'Оформить заказ', continue_shopping: 'Продолжить покупки', product: 'Товар', price: 'Цена', quantity: 'Кол-во' },
    checkout: { title: 'Оформление заказа', address: 'Адрес', payment: 'Оплата', confirm: 'Подтверждение', order_summary: 'Ваш заказ', total_to_pay: 'К оплате', place_order: 'Подтвердить заказ' },
    product: { details: 'Детали', reviews: 'Отзывы', related: 'Похожие товары' }
  },
  pt: {
    nav: { home: 'Início', products: 'Produtos', categories: 'Categorias', search: 'Pesquisar', cart: 'Carrinho', account: 'Minha Conta', login: 'Entrar', menu: 'Menu', menu_desc: 'Categorias', favorites: 'Favoritos' },
    common: { search_placeholder: 'Pesquisar...', search_min_chars: 'Mínimo 2 caracteres.', search_not_found: 'Nenhum resultado.', search_try_again: 'Tente novamente.', search_searching: 'Procurando...', search_suggestions: 'Sugeridos', all_results: 'Ver Tudo', add_to_cart: 'Adicionar', quick_buy: 'Comprar', out_of_stock: 'Esgotado', added: 'Adicionado', loading: 'Carregando...', discount: 'OFERTA', filter: 'Filtrar', clear_filters: 'Limpar', categories: 'Categorias', brands: 'Marcas', price_range: 'Faixa de Preço', in_stock_only: 'Em Estoque', min: 'Min', max: 'Max' },
    footer: {
      free_shipping: 'Frete Grátis', free_shipping_desc: 'Para compras acima de 200 TL', secure_payment: 'Pagamento Seguro', secure_payment_desc: 'SSL 256-bit', easy_return: 'Devoluções Fáceis', easy_return_desc: '14 dias para devolução', support: 'Suporte 24/7', support_desc: 'Estamos com você', newsletter: 'Boletim', newsletter_desc: 'Novidades.', shopping: 'Compras', corporate: 'Corporativo', legal: 'Legal', store_location: 'Nossa Loja', instagram: 'No Instagram', copyright: 'Todos os direitos reservados.', about: 'Sobre Nós', contact: 'Contato', faq: 'FAQ', shipping: 'Frete', policies: 'Políticas', terms: 'Termos', privacy: 'Privacidade', return: 'Devoluções'
    },
    cart: { empty: 'Carrinho Vazio', empty_desc: 'Ainda não há produtos.', total: 'Subtotal', checkout: 'Finalizar', continue_shopping: 'Continuar', product: 'Produto', price: 'Preço', quantity: 'Qtd' },
    checkout: { title: 'Finalizar', address: 'Endereço', payment: 'Pagamento', confirm: 'Confirmar', order_summary: 'Resumo', total_to_pay: 'Total a Pagar', place_order: 'Fazer Pedido' },
    product: { details: 'Detalhes', reviews: 'Avaliações', related: 'Relacionados' }
  },
  es: {
    nav: { home: 'Inicio', products: 'Productos', categories: 'Categorías', search: 'Buscar', cart: 'Carrito', account: 'Mi Cuenta', login: 'Iniciar sesión', menu: 'Menú', menu_desc: 'Categorías', favorites: 'Favoritos' },
    common: { search_placeholder: 'Buscar...', search_min_chars: 'Mínimo 2 caracteres.', search_not_found: 'Sin resultados.', search_try_again: 'Inténtalo de nuevo.', search_searching: 'Buscando...', search_suggestions: 'Sugeridos', all_results: 'Ver Todo', add_to_cart: 'Añadir', quick_buy: 'Comprar', out_of_stock: 'Agotado', added: 'Añadido', loading: 'Cargando...', discount: 'OFERTA', filter: 'Filtrar', clear_filters: 'Limpiar', categories: 'Categorías', brands: 'Marcas', price_range: 'Rango de Precios', in_stock_only: 'En stock', min: 'Min', max: 'Max' },
    footer: {
      free_shipping: 'Envío Gratis', free_shipping_desc: 'Pedidos superiores a 200 TL', secure_payment: 'Pago Seguro', secure_payment_desc: 'SSL de 256-bit', easy_return: 'Devolución Fácil', easy_return_desc: '14 días para devolver', support: 'Soporte 24/7', support_desc: 'Estamos contigo', newsletter: 'Boletín', newsletter_desc: 'Novedades.', shopping: 'Compras', corporate: 'Corporativo', legal: 'Legal', store_location: 'Ubicación', instagram: 'Instagram', copyright: 'Todos los derechos reservados.', about: 'Sobre Nosotros', contact: 'Contacto', faq: 'Preguntas', shipping: 'Envío', policies: 'Políticas', terms: 'Términos', privacy: 'Privacidad', return: 'Devoluciones'
    },
    cart: { empty: 'Carrito Vacío', empty_desc: 'No hay productos aún.', total: 'Subtotal', checkout: 'Comprar', continue_shopping: 'Continuar', product: 'Producto', price: 'Precio', quantity: 'Cant.' },
    checkout: { title: 'Proceso de pago', address: 'Dirección', payment: 'Pago', confirm: 'Confirmar', order_summary: 'Resumen', total_to_pay: 'Total a Pagar', place_order: 'Hacer Pedido' },
    product: { details: 'Detalles', reviews: 'Opiniones', related: 'Relacionados' }
  },
  ar: {
    nav: { home: 'الرئيسية', products: 'المنتجات', categories: 'الفئات', search: 'بحث', cart: 'عربة التسوق', account: 'حسابي', login: 'تسجيل الدخول', menu: 'القائمة', menu_desc: 'روابط', favorites: 'المفضلة' },
    common: { search_placeholder: 'بحث...', search_min_chars: 'أدخل 2 أحرف.', search_not_found: 'لا توجد نتائج.', search_try_again: 'حاول مرة اخرى.', search_searching: 'جاري البحث...', search_suggestions: 'منتجات مقترحة', all_results: 'عرض الكل', add_to_cart: 'أضف للسلة', quick_buy: 'اشتري الآن', out_of_stock: 'نفذت الكمية', added: 'تمت الإضافة', loading: 'جاري التحميل...', discount: 'تخفيض', filter: 'تصفية', clear_filters: 'مسح', categories: 'الفئات', brands: 'الماركات', price_range: 'نطاق السعر', in_stock_only: 'متوفر فقط', min: 'الحد الأدنى', max: 'الحد الأقصى' },
    footer: {
      free_shipping: 'شحن مجاني', free_shipping_desc: 'للطلبات أكثر من 200 ليرة', secure_payment: 'دفع آمن', secure_payment_desc: 'تسوق آمن SSL', easy_return: 'إرجاع سهل', easy_return_desc: '14 يوما للإرجاع', support: 'دعم 24/7', support_desc: 'نحن معك دائما', newsletter: 'النشرة البريدية', newsletter_desc: 'الأخبار.', shopping: 'التسوق', corporate: 'الشركة', legal: 'قانوني', store_location: 'المتجر', instagram: 'انستغرام', copyright: 'كل الحقوق محفوظة.', about: 'معلومات عنا', contact: 'اتصال', faq: 'الأسئلة الشائعة', shipping: 'شحن', policies: 'سياسات', terms: 'شروط', privacy: 'خصوصية', return: 'إرجاع'
    },
    cart: { empty: 'العربة فارغة', empty_desc: 'لا توجد منتجات بعد.', total: 'المجموع الإجمالي', checkout: 'الدفع', continue_shopping: 'مواصلة التسوق', product: 'المنتج', price: 'السعر', quantity: 'الكمية' },
    checkout: { title: 'الدفع', address: 'العنوان', payment: 'الدفع', confirm: 'تأكيد', order_summary: 'ملخص', total_to_pay: 'الإجمالي', place_order: 'تأكيد الطلب' },
    product: { details: 'التفاصيل', reviews: 'التقييمات', related: 'منتجات ذات صلة' }
  }
};

const content = `import { createI18n } from 'vue-i18n';

const messages = ${JSON.stringify(messages, null, 2)};

const i18n = createI18n({
  legacy: false, 
  locale: 'tr', // default
  fallbackLocale: 'en',
  messages,
});

export default i18n;`;

fs.writeFileSync('resources/js/i18n.ts', content);
