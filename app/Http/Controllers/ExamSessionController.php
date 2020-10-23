<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    public function create(Request $request)
    {
        $code = '';
        while (1) {
            $code = Str::random(5);
            if (! ExamSession::firstWhere('code', $code)) {
                break;
            }
        }

        $exam_session = new ExamSession([
            'code' => $code,
            'started_by' => $request->user()->id
        ]);

        $exam_session->save();

        return $exam_session;
    }

    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        ExamSession::where('code', $request->code)->firstOrFail();
    }
}
