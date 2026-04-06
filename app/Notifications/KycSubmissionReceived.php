<?php

namespace App\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KycSubmissionReceived extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly ?string $documentType = null,
    ) {}

    public function via(object $notifiable): array
    {
        if (!empty($notifiable->fcm_token)) {
            SendPushNotification::dispatch(
                $notifiable->fcm_token,
                'Nouvelle demande KYC',
                $this->getMessage(),
                [
                    'type' => 'kyc_submission_received',
                    'user_id' => (string) $this->user->id,
                    'document_type' => (string) ($this->documentType ?? ''),
                ]
            );
        }

        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kyc_submission_received',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'document_type' => $this->documentType,
            'title' => 'Nouvelle demande KYC',
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage(): string
    {
        $message = "{$this->user->name} a soumis une demande de verification KYC";

        if (!empty($this->documentType)) {
            $message .= " ({$this->documentType})";
        }

        return $message . '.';
    }
}
