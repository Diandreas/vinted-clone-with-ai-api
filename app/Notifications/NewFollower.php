<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $follower,
    ) {}

    public function via(object $notifiable): array
    {
        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                '👤 Nouvel abonné !',
                "{$this->follower->name} s'est abonné à votre profil",
                [
                    'type'        => 'new_follower',
                    'follower_id' => (string) $this->follower->id,
                ]
            );
        }

        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'new_follower',
            'follower_id'    => $this->follower->id,
            'follower_name'  => $this->follower->name,
            'follower_avatar' => $this->follower->avatar_url,
            'message'        => "{$this->follower->name} s'est abonné à votre profil",
        ];
    }
}
