<?php

namespace App\Http\Controllers;

use App\Events\PeerConnectionAnswer;
use App\Events\PeerConnectionICE;
use App\Events\PeerConnectionOffer;
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
            'recipient_id' => ['required', 'exists:users,id']
        ]);

        $event = new PeerConnectionOffer(
            $request->exam_code,
            $request->offer,
            $request->user()->id,
            $request->recipient_id
        );
        broadcast($event);
    }

    public function answer(Request $request)
    {
        $request->validate([
            'answer' => 'required',
            'recipient_id' => ['bail', 'required', new IsCandidate()],
            'exam_code' => ['required', 'exists:exam_sessions,code']
        ]);

        $event = new PeerConnectionAnswer(
            $request->exam_code,
            $request->answer,
            $request->user()->id,
            (int) $request->recipient_id,
        );
        broadcast($event);
    }

    public function iceCandidate(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required',
            'ice' => 'required',
            'exam_code' => ['required', 'exists:exam_sessions,code']
        ]);

        $event = new PeerConnectionICE(
            $request->exam_code,
            $request->ice,
            $request->user()->id,
            (int)$request->recipient_id
        );
        broadcast($event);
    }
}
