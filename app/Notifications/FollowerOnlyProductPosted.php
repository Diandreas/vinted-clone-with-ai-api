<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Product;

class FollowerOnlyProductPosted extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'follower_only_product',
            'product_id' => $this->product->id,
            'title' => $this->product->title,
            'user_id' => $this->product->user_id,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Product;

class FollowerOnlyProductPosted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'follower_only_product',
            'product_id' => $this->product->id,
            'title' => $this->product->title,
            'price' => $this->product->price,
            'seller_id' => $this->product->user_id,
            'message' => 'Nouvelle offre réservée aux followers',
        ];
    }
}


