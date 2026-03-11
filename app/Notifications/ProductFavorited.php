<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProductFavorited extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly User $fan,
    ) {}

    public function via(object $notifiable): array
    {
        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                '⭐ Nouveau favori !',
                "{$this->fan->name} a ajouté « {$this->product->title} » à ses favoris",
                [
                    'type'       => 'product_liked',
                    'product_id' => (string) $this->product->id,
                    'liker_id'   => (string) $this->fan->id,
                ]
            );
        }

        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'product_favorited',
            'product_id'    => $this->product->id,
            'product_title' => $this->product->title,
            'fan_id'        => $this->fan->id,
            'fan_name'      => $this->fan->name,
            'fan_avatar'    => $this->fan->avatar_url,
            'message'       => "{$this->fan->name} a ajouté « {$this->product->title} » à ses favoris",
        ];
    }
}
