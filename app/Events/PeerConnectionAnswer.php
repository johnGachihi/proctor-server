<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PeerConnectionAnswer
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $candidate_id;
    public $answer;
    public $senderId;

    public function __construct(int $candidate_id, array $answer, int $senderId)
    {
        $this->candidate_id = $candidate_id;
        $this->answer = $answer;
        $this->senderId = $senderId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('candidate.' . $this->candidate_id);
    }
}
