<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignallingMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $signallingMessage;
    public $recipient;  // TODO: Should the User of id of user be broadcast
    public $sender;

    public function __construct(array $signallingMessage, User $recipient, User $sender)
    {
        $this->recipient = $recipient;
        $this->sender = $sender;
        $this->signallingMessage = $signallingMessage;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('signalling_message.' . $this->recipient->id);
    }
}
