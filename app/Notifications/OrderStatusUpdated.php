<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $statusLabel
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Sipariş Durumu Güncellendi',
            'body' => "{$this->order->order_number} numaralı siparişinizin durumu '{$this->statusLabel}' olarak güncellenmiştir.",
            'action_url' => url('/hesabim/siparisler/'.$this->order->public_id),
        ];
    }
}
