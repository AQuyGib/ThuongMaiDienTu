<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'message_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'reactions' => 'array',
        'is_read' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id', 'room_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}
