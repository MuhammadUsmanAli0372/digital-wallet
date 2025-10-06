<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'status',
    ];

    /**
     * A transaction belongs to a sender (User).
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * A transaction belongs to a receiver (User).
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
