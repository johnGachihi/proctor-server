<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignallingMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $signallingMessage;
    public $recipient;  // TODO: Should the User of id of user be broadcast

    public function __construct(array $signallingMessage, User $recipient)
    {
        $this->recipient = $recipient;
        $this->signallingMessage = $signallingMessage;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('signalling_message.' . $this->recipient->id);
    }
}
