<?php

use App\Models\ExamSession;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// TODO: Remove
Broadcast::channel('signalling_message.{recipientId}', function ($user, $recipientId) {
    if ((int)$user->id === (int)$recipientId) {
        return $user;
    }
    return null;
});

// TODO: Remove
Broadcast::channel('candidate.{candidateId}', function ($user, $candidateId) {
    return (int)$user->id === (int)$candidateId
        && $user->role === 'candidate';
});

// TODO: Remove
Broadcast::channel('proctors.{examCode}', function ($user) {
    return $user->role === 'proctor' ? $user : null;
});

// TODO: Remove
Broadcast::channel('proctor.{proctorId}', function ($user, $proctorId) {
    return (int) $user->id === (int) $proctorId
        && $user->role === 'proctor';
});

Broadcast::channel('exam.{examCode}',function ($user, $examCode) {
    return ExamSession::where('code', $examCode)->count() > 0
        ? ['id' => $user->id, 'name' => $user->name, 'role' => $user->role]
        : null;
});
