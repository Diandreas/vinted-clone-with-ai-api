<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Notification;

class ProductLiked extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly User $liker,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                '❤️ Nouveau like !',
                "{$this->liker->name} a aimé votre produit « {$this->product->title} »",
                [
                    'type'       => 'product_liked',
                    'product_id' => (string) $this->product->id,
                    'liker_id'   => (string) $this->liker->id,
                ]
            );
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'product_liked',
            'product_id'   => $this->product->id,
            'product_title' => $this->product->title,
            'liker_id'     => $this->liker->id,
            'liker_name'   => $this->liker->name,
            'liker_avatar' => $this->liker->avatar_url,
            'message'      => "{$this->liker->name} a aimé votre produit « {$this->product->title} »",
        ];
    }
}
