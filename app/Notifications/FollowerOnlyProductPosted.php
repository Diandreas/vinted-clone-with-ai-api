<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FollowerOnlyProductPosted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly User $seller,
        public readonly bool $followersOnly = true,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (!empty($notifiable->fcm_token)) {
            $title = $this->followersOnly
                ? '🔒 Offre exclusive pour vous !'
                : "🛍️ Nouveau produit de {$this->seller->name}";

            $body = $this->followersOnly
                ? "{$this->seller->name} a publié une offre réservée à ses abonnés : « {$this->product->title} »"
                : "{$this->seller->name} vient de publier « {$this->product->title} » — {$this->product->price} XAF";

            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                $title,
                $body,
                [
                    'type'           => 'product_published',
                    'product_id'     => (string) $this->product->id,
                    'seller_id'      => (string) $this->seller->id,
                    'followers_only' => $this->followersOnly ? '1' : '0',
                ]
            );
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'product_published',
            'product_id'     => $this->product->id,
            'product_title'  => $this->product->title,
            'product_price'  => $this->product->price,
            'seller_id'      => $this->seller->id,
            'seller_name'    => $this->seller->name,
            'followers_only' => $this->followersOnly,
            'message'        => $this->followersOnly
                ? "Nouvelle offre exclusive de {$this->seller->name}"
                : "Nouveau produit de {$this->seller->name}",
        ];
    }
}
