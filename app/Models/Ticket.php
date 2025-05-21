<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'code',
        'description',
        'status',
        'priority',
        'completed_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // merelasikan table user ('user_id')
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class); // merelasikan field ticket->id ke table ticketreply
    }
}
