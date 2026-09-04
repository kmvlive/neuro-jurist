<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageFeedback extends Model
{
    protected $table = 'message_feedbacks';

    protected $fillable = ['message_id', 'vote', 'comment', 'guest_id', 'user_id'];

    protected $casts = [
        'vote' => 'integer',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
