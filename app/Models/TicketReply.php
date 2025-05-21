<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class); // merelasikan table ticket ('ticket_id')
    }

    public function user()
    {
        return $this->belongsTo(User::class); // merelasikan table user ('user_id')
    }
}
