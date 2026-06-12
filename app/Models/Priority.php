<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level', 'description'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function keywords()
    {
        return $this->hasMany(PriorityKeyword::class);
    }

    public function sla()
    {
        return $this->hasOne(Sla::class);
    }
}
