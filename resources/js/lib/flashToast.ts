import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    const handleToast = (event: any) => {
        const props = event.detail?.page?.props || {};
        const data = props.flash?.toast as FlashToast | undefined;

        if (data && data.message) {
            toast[data.type || 'info'](data.message);
            // Clear toast to prevent duplicates
            props.flash.toast = null;
        }
    };

    router.on('success', handleToast);
    router.on('navigate', handleToast);
}
