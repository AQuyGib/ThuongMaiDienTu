<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RepairTicket extends Model {
    protected $primaryKey = 'ticket_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function technician() {
        return $this->belongsTo(User::class, 'technician_id', 'user_id');
    }
    public function articles() {
        return $this->hasMany(Article::class, 'related_ticket_id', 'ticket_id');
    }
}