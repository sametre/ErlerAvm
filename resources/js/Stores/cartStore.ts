import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface CartItem {
    id: number;
    public_id: string;
    name: string;
    price: number;
    quantity: number;
    image_url?: string;
}

export const useCartStore = defineStore(
    'cart',
    () => {
        const items = ref<CartItem[]>([]);

        const totalItems = computed(() =>
            items.value.reduce((total, item) => total + item.quantity, 0),
        );

        const totalPrice = computed(() =>
            items.value.reduce(
                (total, item) => total + item.price * item.quantity,
                0,
            ),
        );

        function addItem(product: CartItem) {
            const existingItem = items.value.find(
                (item) => item.id === product.id,
            );
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                items.value.push(product);
            }
        }

        function removeItem(productId: number) {
            const index = items.value.findIndex(
                (item) => item.id === productId,
            );
            if (index !== -1) {
                items.value.splice(index, 1);
            }
        }

        function updateQuantity(productId: number, quantity: number) {
            const item = items.value.find((item) => item.id === productId);
            if (item) {
                item.quantity = quantity;
            }
        }

        function clearCart() {
            items.value = [];
        }

        return {
            items,
            totalItems,
            totalPrice,
            addItem,
            removeItem,
            updateQuantity,
            clearCart,
        };
    },
    {
        persist: true,
    },
);
