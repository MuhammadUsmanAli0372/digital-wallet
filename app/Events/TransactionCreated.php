<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $senderId;
    public $receiverId;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['sender', 'receiver']);
        $this->senderId = $transaction->sender_id;
        $this->receiverId = $transaction->receiver_id;
    }

    /**
     * The channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("transactions.{$this->senderId}"),
            new PrivateChannel("transactions.{$this->receiverId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'transaction' => $this->transaction,
            'balance' => [
                'sender' => $this->transaction->sender->balance,
                'receiver' => $this->transaction->receiver->balance,
            ],
        ];
    }
}
