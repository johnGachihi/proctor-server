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

    public $recipient_id;
    public $sender_id;
    public $ice;

    public function __construct(int $recipient_id, int $sender_id, array $ice)
    {
        $this->recipient_id = $recipient_id;
        $this->sender_id = $sender_id;
        $this->ice = $ice;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channel_prefix = User::find($this->recipient_id)->role === 'proctor'
            ? 'proctor'
            : 'candidate';

        return new PrivateChannel($channel_prefix . '.' . $this->recipient_id);
    }
}
