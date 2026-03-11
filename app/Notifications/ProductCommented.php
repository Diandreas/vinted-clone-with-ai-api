<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Product;
use App\Models\ProductComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProductCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly ProductComment $comment,
        public readonly User $commenter,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                '💬 Nouveau commentaire sur votre produit',
                "{$this->commenter->name} : {$this->comment->content}",
                [
                    'type'       => 'product_commented',
                    'product_id' => (string) $this->product->id,
                    'comment_id' => (string) $this->comment->id,
                    'user_id'    => (string) $this->commenter->id,
                ]
            );
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'product_commented',
            'product_id'     => $this->product->id,
            'product_title'  => $this->product->title,
            'comment_id'     => $this->comment->id,
            'comment'        => $this->comment->content,
            'commenter_id'   => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'commenter_avatar' => $this->commenter->avatar_url,
            'message'        => "{$this->commenter->name} a commenté votre produit « {$this->product->title} »",
        ];
    }
}
