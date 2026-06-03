<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = 'chat_rooms';
    protected $primaryKey = 'room_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'room_id', 'room_id')->orderBy('created_at', 'asc');
    }

    public function members()
    {
        return $this->hasMany(ChatRoomMember::class, 'room_id', 'room_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_members', 'room_id', 'user_id', 'room_id', 'user_id')
                    ->withPivot('room_role')
                    ->withTimestamps();
    }
}
