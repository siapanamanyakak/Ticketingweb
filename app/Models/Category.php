<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'base_priority',
        'max_priority',
        'is_active',
    ];

    public function keywords()
    {
        return $this->hasMany(CategoryKeyword::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->withTrashed();
    }
}
