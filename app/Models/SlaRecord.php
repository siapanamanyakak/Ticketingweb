<?php

namespace App\Models;

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
}
