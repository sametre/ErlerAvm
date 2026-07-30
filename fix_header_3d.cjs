const fs = require('fs');

let content = fs.readFileSync('resources/js/components/storefront/StorefrontHeader.vue', 'utf8');

// Import Category3DIcon
content = content.replace(
    "import BrandLogo from '@/components/brand/BrandLogo.vue';",
    "import BrandLogo from '@/components/brand/BrandLogo.vue';\nimport Category3DIcon from './Category3DIcon.vue';"
);

// Replace img
content = content.replace(
    /<div class="relative h-28 w-full shrink-0 flex items-center justify-center transition-transform duration-300 group-hover\/item:scale-110">\s*<img\s*:src="category\.image \|\| groupImage\(category\.title\)"\s*:alt="category\.title"\s*class="max-h-full max-w-full object-contain drop-shadow-\[0_8px_16px_rgba\(0,0,0,0\.15\)\]"\s*\/>\s*<\/div>/,
    '<Category3DIcon :title="category.title" class="group-hover/item:scale-110 transition-transform duration-300" />'
);

fs.writeFileSync('resources/js/components/storefront/StorefrontHeader.vue', content);
