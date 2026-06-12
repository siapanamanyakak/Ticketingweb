<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorityKeyword extends Model
{
    use HasFactory;

    protected $fillable = ['priority_id', 'keyword', 'weight'];

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }
}
