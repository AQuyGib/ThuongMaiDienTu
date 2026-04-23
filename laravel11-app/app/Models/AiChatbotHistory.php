<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AiChatbotHistory extends Model {
    protected $table = 'ai_chatbot_history';
    protected $primaryKey = 'chat_id';
    const UPDATED_AT = null;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}