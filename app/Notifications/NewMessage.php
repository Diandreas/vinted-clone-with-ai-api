<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Message $message,
        public readonly User $sender,
        public readonly Conversation $conversation,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $productTitle = $this->conversation->product?->title;
        $body = $productTitle
            ? "{$this->sender->name} : {$this->message->content} (à propos de « {$productTitle} »)"
            : "{$this->sender->name} : {$this->message->content}";

        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                '💬 Nouveau message',
                $body,
                [
                    'type'            => 'new_message',
                    'conversation_id' => (string) $this->conversation->id,
                    'message_id'      => (string) $this->message->id,
                    'sender_id'       => (string) $this->sender->id,
                    'product_id'      => (string) ($this->conversation->product_id ?? ''),
                ]
            );
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'new_message',
            'conversation_id' => $this->conversation->id,
            'message_id'      => $this->message->id,
            'sender_id'       => $this->sender->id,
            'sender_name'     => $this->sender->name,
            'sender_avatar'   => $this->sender->avatar_url,
            'content'         => $this->message->content,
            'product_id'      => $this->conversation->product_id,
            'product_title'   => $this->conversation->product?->title,
            'message'         => "{$this->sender->name} vous a envoyé un message",
        ];
    }
}
