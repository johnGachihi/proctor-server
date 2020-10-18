<?php

namespace Tests\Feature;

use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExamSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test__create__when_user_unauthenticated()
    {
        $response = $this->json('POST', 'api/exam-session');
        $response->assertUnauthorized();
    }

    public function test__create__when_user_is_not_proctor()
    {
        $user = User::factory()->create([
            'role' => 'candidate'
        ]);
        $response = $this->actingAs($user)->json('POST', 'api/exam-session');

        $response->assertUnauthorized();
    }

    public function test__create__creates_exam_session_entity()
    {
        $user = User::factory()->create([
            'role' => 'proctor'
        ]);
        $response = $this->actingAs($user)
            ->json('POST', 'api/exam-session');

        $this->assertDatabaseCount('exam_sessions', 1);
    }

    public function test__create__creates_exam_session_with_user_id_of_creator()
    {
        $user = User::factory()->create([
            'role' => 'proctor'
        ]);
        $this->actingAs($user)->json('POST', 'api/exam-session');

        $this->assertDatabaseHas('exam_sessions', [
            'started_by' => $user->id
        ]);
    }

    public function test__create__returns_created_exam_session()
    {
        $user = User::factory()->create([
            'role' => 'proctor'
        ]);
        $response = $this->actingAs($user)
            ->json('POST', 'api/exam-session');

        $examSession = ExamSession::find(1);
        $response->assertJson($examSession->toArray());
    }
}
