<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'type',
        'created_by', 'is_active',
        'starts_at', 'ends_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    // Scope: hanya news yang aktif dan belum expired
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('ends_at')
                           ->orWhere('ends_at', '>=', now());
                     })
                     ->where(function ($q) {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', now());
                     });
    }

    // Helper type styling
    public function getTypeColorAttribute(): array
    {
        return match($this->type) {
            'warning'     => ['bg' => '#fef3c7', 'color' => '#b45309', 'border' => '#fde68a', 'icon' => '⚠️'],
            'maintenance' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'border' => '#fed7aa', 'icon' => '🔧'],
            default       => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => '📢'],
        };
    }
}
