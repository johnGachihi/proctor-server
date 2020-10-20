<?php

namespace App\Http\Controllers;

use App\Events\SignallingMessageSent;
use App\Models\User;
use App\Rules\IsCandidate;
use App\Rules\IsProctor;
use Illuminate\Http\Request;

class SignallingController extends Controller
{
    public function offer(Request $request)
    {
        $request->validate([
            'recipient_id' => ['bail', 'required', new IsProctor()],
            'signalling_message' => 'required',
        ]);

        $this->broadcastMessage(
            $request->signalling_message,
            $request->recipient_id
        );
    }

    public function answer(Request $request)
    {
        $request->validate([
            'recipient_id' => ['bail', 'required', new IsCandidate()],
            'signalling_message' => 'required'
        ]);

        $this->broadcastMessage(
            $request->signalling_message,
            $request->recipient_id
        );
    }

    public function trickleICE(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required',
            'signalling_message' => 'required'
        ]);

        $this->broadcastMessage(
            $request->signalling_message,
            $request->recipient_id
        );
    }

    private function broadcastMessage($signallingMessage, $recipient_id)
    {
        $recipient = User::find($recipient_id);
        $event = new SignallingMessageSent($signallingMessage, $recipient);
        broadcast($event);
    }
}
