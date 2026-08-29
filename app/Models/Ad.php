<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = ['quick_prompt_id', 'content', 'cta_text', 'cta_url', 'active'];

    public function quickPrompt()
    {
        return $this->belongsTo(QuickPrompt::class);
    }
}
