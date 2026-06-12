<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'category_id',
        'priority_id',
        'title',
        'description',
        'attachment',
        'status',
        'resolution_notes',
        'first_response_at',
        'pending_at',
        'pending_duration',
        'had_pending',
        'pending_count',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'pending_at'        => 'datetime',
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
        'had_pending'       => 'boolean',
    ];

    // ── Auto Generate Ticket Number ───────────────
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $year  = now()->format('Y');
            $month = now()->format('m');

            $count = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->count() + 1;

            $ticket->ticket_number = 'KTU-' . $year . $month . '-#' . str_pad($count, 3, '0', STR_PAD_LEFT);
        });
    }

    // ── Relationships ─────────────────────────────
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function slaRecord()
    {
        return $this->hasOne(SlaRecord::class);
    }

    public function slaPauses()
    {
        return $this->hasMany(SlaPause::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    // ── Status Helpers ────────────────────────────
    public function isOpen(): bool       { return $this->status === 'open'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isResolved(): bool   { return $this->status === 'resolved'; }
    public function isClosed(): bool     { return $this->status === 'closed'; }

    // ── SLA Helpers ───────────────────────────────
    public function isSlaBreached(): bool
    {
        if (!$this->slaRecord) return false;
        return now()->gt($this->slaRecord->resolution_deadline)
            && !in_array($this->status, ['resolved', 'closed']);
    }
}
