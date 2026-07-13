<?php

namespace App\Models;

use App\Services\SlaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'response_deadline',
        'resolution_deadline',
        'response_met_at',
        'resolution_met_at',
        'total_paused_minutes',
        'response_breached',
        'resolution_breached',
    ];

    protected $casts = [
        'response_deadline'    => 'datetime',
        'resolution_deadline'  => 'datetime',
        'response_met_at'      => 'datetime',
        'resolution_met_at'    => 'datetime',
        'response_breached'    => 'boolean',
        'resolution_breached'  => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function pauses()
    {
        return $this->hasMany(SlaPause::class, 'ticket_id', 'ticket_id');
    }

    // ── ATURAN 3: Accessor Virtual — Waktu Kerja Bersih ──
    // Tidak ada field baru di database
    // Rumus: getWorkingMinutesBetween(created_at, resolution_met_at) - total_paused_minutes
    public function getActualWorkingMinutesAttribute(): int
    {
        if (!$this->resolution_met_at || !$this->ticket) return 0;

        $slaService = app(SlaService::class);

        $grossMinutes = $slaService->getWorkingMinutesBetween(
            $this->ticket->created_at,
            $this->resolution_met_at
        );

        return max(0, $grossMinutes - $this->total_paused_minutes);
    }
}
