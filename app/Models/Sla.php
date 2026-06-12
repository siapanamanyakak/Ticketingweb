<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;
    protected $table = 'sla';

    protected $fillable = [
        'priority_id',
        'response_time',
        'resolution_time',
        'working_hours_only',
    ];

    protected $casts = ['working_hours_only' => 'boolean'];

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function slaRecords()
    {
        return $this->hasMany(SlaRecord::class);
    }
}
