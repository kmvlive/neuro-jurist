<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['path', 'ip', 'user_id'];

    protected $casts = ['created_at' => 'datetime'];
}
