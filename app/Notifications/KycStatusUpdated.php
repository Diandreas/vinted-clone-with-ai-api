<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KycStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $status,
        public readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                $this->getTitle(),
                $this->getMessage(),
                [
                    'type' => 'kyc_status_updated',
                    'status' => $this->status,
                    'reason' => $this->reason,
                ]
            );
        }

        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kyc_status_updated',
            'status' => $this->status,
            'reason' => $this->reason,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
        ];
    }

    private function getTitle(): string
    {
        return $this->status === 'verified'
            ? 'Verification KYC approuvee'
            : 'Verification KYC rejetee';
    }

    private function getMessage(): string
    {
        if ($this->status === 'verified') {
            return 'Votre verification d\'identite a ete approuvee. Vous pouvez maintenant acceder aux fonctionnalites reservees aux comptes verifies.';
        }

        $message = 'Votre verification d\'identite a ete rejetee.';

        if (!empty($this->reason)) {
            $message .= ' Motif : ' . $this->reason;
        }

        return $message;
    }
}
