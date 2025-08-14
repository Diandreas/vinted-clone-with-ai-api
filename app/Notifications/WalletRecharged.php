<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\WalletTransaction;

class WalletRecharged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->transaction->amount_xaf) . ' FCFA';
        
        return (new MailMessage)
            ->subject('Wallet rechargé avec succès')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre wallet a été rechargé avec succès.')
            ->line('Montant : ' . $amount)
            ->line('Méthode : ' . $this->transaction->provider_label)
            ->line('Date : ' . $this->transaction->created_at->format('d/m/Y H:i'))
            ->action('Voir mon wallet', url('/wallet'))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_recharged',
            'title' => 'Wallet rechargé',
            'message' => 'Votre wallet a été rechargé de ' . number_format($this->transaction->amount_xaf) . ' FCFA',
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount_xaf,
            'provider' => $this->transaction->provider,
            'data' => [
                'transaction_id' => $this->transaction->id,
                'amount_xaf' => $this->transaction->amount_xaf,
                'provider' => $this->transaction->provider,
                'status' => $this->transaction->status,
            ],
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
