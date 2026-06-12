<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_staff',
        'username',
        'department_id',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── Role Helpers ──────────────────────────────
    public function isUser(): bool         { return $this->role === 'user'; }
    public function isItSupport(): bool    { return $this->role === 'it_support'; }
    public function isItSupervisor(): bool { return $this->role === 'it_supervisor'; }

    // ── Relationships ─────────────────────────────
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function ticketLogs()
    {
        return $this->hasMany(TicketLog::class, 'updated_by');
    }
}
