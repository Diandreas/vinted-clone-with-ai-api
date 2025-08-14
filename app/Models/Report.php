<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status', // 'pending', 'investigating', 'resolved', 'dismissed'
        'admin_notes',
        'resolved_at',
        'resolved_by',
        'severity', // 'low', 'medium', 'high', 'critical'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'severity' => 'string',
    ];

    /**
     * Get the user who made the report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the admin who resolved the report.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the reportable entity (product, user, live, etc.).
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get reports by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get reports by severity.
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to get critical reports.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Mark report as resolved.
     */
    public function markAsResolved($adminId, $notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'admin_notes' => $notes,
            'resolved_at' => now(),
            'resolved_by' => $adminId,
        ]);
    }

    /**
     * Mark report as dismissed.
     */
    public function markAsDismissed($adminId, $notes = null)
    {
        $this->update([
            'status' => 'dismissed',
            'admin_notes' => $notes,
            'resolved_at' => now(),
            'resolved_by' => $adminId,
        ]);
    }

    /**
     * Check if report is resolved.
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'dismissed']);
    }

    /**
     * Check if report is critical.
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }
}
