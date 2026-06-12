<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPause extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'reason',
        'paused_at',
        'resumed_at',
        'duration_minutes',
    ];

    protected $casts = [
        'paused_at'  => 'datetime',
        'resumed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
