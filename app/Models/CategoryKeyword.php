<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryKeyword extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'keyword', 'weight'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
