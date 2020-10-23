<?php

namespace App\Http\Controllers;

use App\Events\PeerConnectionAnswer;
use App\Events\PeerConnectionICE;
use App\Events\PeerConnectionOffer;
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
            'exam_code' => ['required', 'exists:exam_sessions,code'],
            'offer' => 'required',
        ]);

        broadcast(new PeerConnectionOffer($request->exam_code, $request->offer));
    }

    public function answer(Request $request)
    {
        $request->validate([
            'candidate_id' => ['bail', 'required', new IsCandidate()],
            'answer' => 'required'
        ]);

        broadcast(new PeerConnectionAnswer((int) $request->candidate_id, $request->answer));
    }

    public function iceCandidate(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required',
            'ice' => 'required'
        ]);

        broadcast(new PeerConnectionICE(
            (int) $request->recipient_id, $request->user()->id, $request->ice));
    }
}
