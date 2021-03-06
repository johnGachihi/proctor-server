<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PeerConnectionICE implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $recipientId;
    public $senderId;
    public $ice;
    public $examCode;

    public function __construct(string $examCode, array $ice, int $senderId, int $recipientId)
    {
        $this->recipientId = $recipientId;
        $this->senderId = $senderId;
        $this->ice = $ice;
        $this->examCode = $examCode;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('exam.' . $this->examCode);
    }
}
