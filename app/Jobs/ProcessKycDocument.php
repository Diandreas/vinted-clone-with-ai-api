<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessKycDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $documentPath,
        public ?string $documentType = null
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        // Basic auto-check placeholder: ensure file exists and type is expected.
        if (!Storage::exists($this->documentPath)) {
            $user->update([
                'kyc_status' => 'rejected',
                'kyc_rejection_reason' => 'Document introuvable.',
                'kyc_verified_at' => null,
            ]);
            return;
        }

        if (!in_array($this->documentType, ['cni', 'passport'], true)) {
            $user->update([
                'kyc_status' => 'rejected',
                'kyc_rejection_reason' => 'Type de document invalide.',
                'kyc_verified_at' => null,
            ]);
            return;
        }

        // Placeholder for OCR/auto-verification.
        // For now, keep pending and require manual review.
        if ($user->kyc_status !== 'pending') {
            $user->update(['kyc_status' => 'pending']);
        }
    }
}
