<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { MessageCircle, Star } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

type Metric = {
    label: string;
    value: number;
};

type ReviewRow = {
    public_id: string;
    rating: number;
    comment: string;
    admin_reply?: string | null;
    status: 'pending' | 'approved' | 'rejected';
    answered_at?: string | null;
    created_at?: string | null;
    product: { name?: string | null; public_id?: string | null };
    user: { name?: string | null; email?: string | null };
};

const props = defineProps<{
    metrics: Metric[];
    reviews: {
        data: ReviewRow[];
    };
}>();

const selectedReview = ref<ReviewRow | null>(null);
const replyForm = useForm({
    status: 'approved' as ReviewRow['status'],
    admin_reply: '',
});

function updateReview(review: ReviewRow, status: ReviewRow['status']): void {
    useForm({ status }).patch(`/yorumlar/${review.public_id}`, {
        preserveScroll: true,
    });
}

function openReply(review: ReviewRow): void {
    selectedReview.value = review;
    replyForm.status = review.status === 'rejected' ? 'pending' : review.status;
    replyForm.admin_reply = review.admin_reply || '';
    replyForm.clearErrors();
}

function submitReply(): void {
    if (!selectedReview.value) return;

    replyForm.patch(`/yorumlar/${selectedReview.value.public_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            selectedReview.value = null;
            replyForm.reset();
        },
    });
}

function deleteReview(review: ReviewRow): void {
    if (!window.confirm('Bu yorumu silmek istediğinize emin misiniz?')) {
        return;
    }

    useForm({}).delete(`/yorumlar/${review.public_id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Yorum Yönetimi" />

    <div class="space-y-6 p-4">
        <section>
            <p class="text-sm font-medium text-primary">Admin moderasyon</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Ürün yorumları
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-muted-foreground">
                Müşteri yorumları varsayılan olarak onaya düşer. Onaylanan
                yorumlar ürün detayında yayınlanır; reddedilenler müşteriye ait
                kayıt olarak saklanır ama vitrinde görünmez.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div
                v-for="metric in props.metrics"
                :key="metric.label"
                class="rounded-lg border bg-card p-5"
            >
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
        </section>

        <section class="rounded-lg border bg-card">
            <div class="flex items-center gap-2 border-b p-4">
                <Star class="size-5 text-primary" />
                <h2 class="font-semibold">Yorum listesi</h2>
            </div>
            <div class="divide-y">
                <article
                    v-for="review in props.reviews.data"
                    :key="review.public_id"
                    class="grid gap-4 p-4 xl:grid-cols-[1fr_auto]"
                >
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <p class="font-semibold">
                                {{ review.product.name || 'Ürün silinmiş' }}
                            </p>
                            <span
                                class="rounded-md bg-muted px-2 py-1 text-xs font-semibold"
                            >
                                {{ review.rating }}/5
                            </span>
                            <span class="rounded-md bg-muted px-2 py-1 text-xs">
                                {{ review.status }}
                            </span>
                        </div>
                        <p class="text-sm leading-6 text-muted-foreground">
                            {{ review.comment }}
                        </p>
                        <div
                            v-if="review.admin_reply"
                            class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-950 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-100"
                        >
                            <p class="font-semibold">Admin cevabı</p>
                            <p class="mt-1 leading-6">
                                {{ review.admin_reply }}
                            </p>
                            <p
                                v-if="review.answered_at"
                                class="mt-2 text-xs opacity-70"
                            >
                                {{ review.answered_at }}
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{
                                review.user.name ||
                                review.user.email ||
                                'Müşteri'
                            }}
                            · {{ review.created_at || '-' }}
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-2 xl:justify-end"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="updateReview(review, 'approved')"
                        >
                            Onayla
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="updateReview(review, 'rejected')"
                        >
                            Reddet
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="openReply(review)"
                        >
                            <MessageCircle class="mr-2 size-4" />
                            Cevapla
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="deleteReview(review)"
                        >
                            Sil
                        </Button>
                    </div>
                </article>
                <p
                    v-if="!props.reviews.data.length"
                    class="p-8 text-center text-sm text-muted-foreground"
                >
                    Henüz ürün yorumu yok.
                </p>
            </div>
        </section>
    </div>

    <div
        v-if="selectedReview"
        class="fixed inset-0 z-50 bg-black/30"
        @click.self="selectedReview = null"
    >
        <aside
            class="ml-auto flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-[#07110f]"
        >
            <header class="border-b p-5">
                <p class="text-sm font-medium text-primary">Yorum cevabı</p>
                <h2 class="mt-1 text-xl font-semibold">
                    {{ selectedReview.product.name || 'Ürün yorumu' }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{
                        selectedReview.user.name ||
                        selectedReview.user.email ||
                        'Müşteri'
                    }}
                </p>
            </header>

            <form class="flex flex-1 flex-col" @submit.prevent="submitReply">
                <div class="flex-1 space-y-5 overflow-y-auto p-5">
                    <section class="rounded-lg border p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <Star class="size-4 text-primary" />
                            <span class="font-semibold"
                                >{{ selectedReview.rating }}/5</span
                            >
                        </div>
                        <p class="text-sm leading-6 text-muted-foreground">
                            {{ selectedReview.comment }}
                        </p>
                    </section>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium">Cevap durumu</label>
                        <select
                            v-model="replyForm.status"
                            class="h-10 rounded-md border bg-background px-3 text-sm"
                        >
                            <option value="pending">Beklemede</option>
                            <option value="approved">
                                Onaylı ve cevaplandı
                            </option>
                            <option value="rejected">Reddedildi</option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium"
                            >Müşteriye cevap</label
                        >
                        <textarea
                            v-model="replyForm.admin_reply"
                            required
                            maxlength="3000"
                            class="min-h-40 rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="Müşteriye görünecek cevabı yazın..."
                        />
                        <p
                            v-if="replyForm.errors.admin_reply"
                            class="text-sm text-destructive"
                        >
                            {{ replyForm.errors.admin_reply }}
                        </p>
                    </div>
                </div>

                <footer class="grid gap-3 border-t p-5 sm:grid-cols-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="selectedReview = null"
                        >Vazgeç</Button
                    >
                    <Button type="submit" :disabled="replyForm.processing"
                        >Cevabı kaydet</Button
                    >
                </footer>
            </form>
        </aside>
    </div>
</template>
