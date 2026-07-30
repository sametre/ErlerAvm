<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';

const page = usePage();
const popupCampaign = page.props.popupCampaign as any;

const isOpen = ref(false);

// Local storage keeps track of whether the user has seen THIS specific campaign ID.
const seenCampaigns = useStorage<string[]>('seen_popup_campaigns', []);

onMounted(() => {
    if (popupCampaign) {
        // If the current campaign's ID is not in the seen array, show the popup.
        if (!seenCampaigns.value.includes(popupCampaign.public_id)) {
            // Small delay so it feels like a "pop-up" after load
            setTimeout(() => {
                isOpen.value = true;
            }, 1000);
        }
    }
});

watch(isOpen, (newVal) => {
    // When the dialog is closed, mark it as seen
    if (
        !newVal &&
        popupCampaign &&
        !seenCampaigns.value.includes(popupCampaign.public_id)
    ) {
        seenCampaigns.value.push(popupCampaign.public_id);
    }
});

const handleClick = () => {
    if (popupCampaign?.target_url) {
        isOpen.value = false;
        window.location.href = popupCampaign.target_url;
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <!-- We override the close button design by rendering it inside DialogContent via Shadcn or by custom styling -->
        <DialogContent
            class="overflow-hidden border-none bg-transparent p-0 shadow-2xl sm:max-w-[600px]"
        >
            <!-- Hidden Title for Accessibility -->
            <DialogTitle class="sr-only">{{
                popupCampaign?.name || 'Kampanya'
            }}</DialogTitle>

            <div
                v-if="popupCampaign"
                class="group relative cursor-pointer"
                @click="handleClick"
            >
                <img
                    :src="popupCampaign.image_path"
                    :alt="popupCampaign.name"
                    class="h-auto w-full rounded-xl object-contain"
                />

                <div
                    v-if="popupCampaign.target_url"
                    class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/5"
                ></div>
            </div>
        </DialogContent>
    </Dialog>
</template>
