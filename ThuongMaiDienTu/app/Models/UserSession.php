<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model {
    protected $primaryKey = 'session_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}