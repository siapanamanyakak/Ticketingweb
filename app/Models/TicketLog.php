<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'updated_by',
        'field_changed',
        'status_before',
        'status_after',
        'note',
        'visibility',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
