<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PeerConnectionOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $examCode;
    public $offer;
    public $senderId;
    public $recipientId;

    public function __construct(string $examCode, array $offer, int $senderId, int $recipientId)
    {
        $this->examCode = $examCode;
        $this->offer = $offer;
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
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
