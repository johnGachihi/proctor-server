<?php

namespace Tests\Feature;

use App\Events\PeerConnectionAnswer;
use App\Events\PeerConnectionICE;
use App\Events\PeerConnectionOffer;
use App\Events\SignallingMessageSent;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SignallingControllerTest extends TestCase
{
    use RefreshDatabase;

    private $valid_offer_request_params;
    private $valid_answer_request_params;
    private $offer_recipient;
    private $answer_recipient;
    private $signalling_message;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->signalling_message = ['foo' => 'bar'];

        $this->offer_recipient = User::factory()->create([
            'id' => 7,
            'role' => 'proctor'
        ]);

        $this->valid_offer_request_params = [
            'recipient_id' => $this->offer_recipient->id,
            'signalling_message' => $this->signalling_message
        ];

        $this->answer_recipient = User::factory()->create([
            'id' => 8,
            'role' => 'candidate'
        ]);

        $this->valid_answer_request_params = [
            'recipient_id' => $this->answer_recipient->id,
            'signalling_message' => $this->signalling_message
        ];

        $this->user = User::factory()->create();
    }

    public function test__offer__when_unauthenticated()
    {
        $response = $this->json('post', 'api/signalling/offer');

        $response->assertUnauthorized();
    }

    public function test__offer__validates_request_param_checks_not_null()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/offer');

        $response->assertJsonValidationErrors([
            'offer' => 'The offer field is required',
            'exam_code' => 'The exam code field is required',
            'recipient_id' => 'The recipient id field is required'
        ]);
    }

    public function test__offer__validates_exam_code_param_exists_in_database()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/offer', ['exam_code' => 12345]);

        $response->assertJsonValidationErrors([
            'exam_code' => 'The selected exam code is invalid',
        ]);
    }

    public function test__offer__validates_recipient_id_is_for_valid_user()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/offer', ['recipient_id' => 12345]);

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The selected recipient id is invalid',
        ]);
    }

    public function test__offer__with_valid_request_params()
    {
        $examSession = ExamSession::factory()->create();
        $recipient = User::factory()->create(['id' => 12345]);

        $response = $this->actingAs($this->user)->json('post', 'api/signalling/offer', [
            'exam_code' => $examSession->code,
            'offer' => ['foo' => 'bar'],
            'recipient_id' => $recipient->id
        ]);

        $response->assertJsonMissingValidationErrors([
            'exam_code', 'offer', 'recipient_id'
        ]);
    }

    public function test__offer__dispatches_broadcast_event()
    {
        $examCode = ExamSession::factory()->create()->code;
        $recipient = User::factory()->create(['id' => 12345]);
        $offer = ['offer' => 'offer'];

        $this->actingAs($this->user)->json('post', 'api/signalling/offer', [
            'exam_code' => $examCode,
            'offer' => $offer,
            'recipient_id' => $recipient->id
        ]);

        Event::assertDispatched(
            function (PeerConnectionOffer $event) use ($examCode, $offer, $recipient) {
                return (string)$event->examCode === (string)$examCode
                    && $event->offer === $offer
                    && $event->senderId === $this->user->id
                    && $event->recipientId === $recipient->id;
            }
        );
    }

    public function test__answer__when_unauthenticated()
    {
        $response = $this->json('post', 'api/signalling/answer');

        $response->assertUnauthorized();
    }

    public function test__answer__validates_request_param_checks_not_null()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/answer');

        $response->assertJsonValidationErrors([
            'answer' => 'The answer field is required',
            'recipient_id' => 'The recipient id field is required',
            'exam_code' => 'The exam code field is required'
        ]);
    }

    public function test__answer__validates_exam_code_exists_in_database()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/answer', ['exam_code' => "a23ca"]);

        $response->assertJsonValidationErrors([
            'exam_code' => 'The selected exam code is invalid']);
    }

    public function test__answer__validates_recipient_id_is_of_valid_user()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/answer', ['recipient_id' => 12345]);

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The selected recipient id is invalid']);
    }

    public function test__answer__with_valid_request_params()
    {
        $answer = ['foo' => 'bar'];
        $recipient_id = User::factory()->create()->id;
        $examSession = ExamSession::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->json('post', 'api/signalling/answer', [
                'answer' => $answer,
                'recipient_id' => $recipient_id,
                'exam_code' => $examSession->code
            ]);

        $response->assertOk();
    }

    public function test__answer__dispatches_broadcast_event()
    {
        $answer = ['foo' => 'bar'];
        $recipient_id = User::factory()->create()->id;
        $examSession = ExamSession::factory()->create();

        $response = $this->actingAs($this->user)->json('post', 'api/signalling/answer', [
            'answer' => $answer,
            'recipient_id' => $recipient_id,
            'exam_code' => $examSession->code
        ]);

//        Event::assertDispatched(PeerConnectionAnswer::class);
        Event::assertDispatched(
            function (PeerConnectionAnswer $event) use ($answer, $recipient_id, $examSession) {
                return $event->recipientId === $recipient_id
                    && $event->answer === $answer
                    && $event->senderId === $this->user->id
                    && $event->examCode === $examSession->code;
            }
        );
    }

    public function test__ice_candidate__when_unauthenticated()
    {
        $response = $this->json('post', 'api/signalling/ice-candidate');

        $response->assertUnauthorized();
    }

    public function test__ice_candidate__validates_request_param_checks_not_null()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/ice-candidate');

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The recipient id field is required',
            'ice' => 'The ice field is required',
            'exam_code' => 'The exam code field is required'
        ]);
    }

    public function test__ice_candidate__validates_exam_code_exists_in_database()
    {
        $response = $this->actingAs($this->user)->json(
            'post', 'api/signalling/ice-candidate', ['exam_code' => "a23ca"]);

        $response->assertJsonValidationErrors([
            'exam_code' => 'The selected exam code is invalid']);
    }

    public function test__ice_candidate__with_valid_request_params()
    {
        $recipient_id = User::factory()->create()->id;
        $ice = ['foo' => 'bar'];
        $examCode = ExamSession::factory()->create()->code;

        $response = $this
            ->actingAs($this->user)
            ->json('post', 'api/signalling/ice-candidate', [
                'recipient_id' => $recipient_id,
                'ice' => $ice,
                'exam_code' => $examCode
            ]);

        $response->assertOk();
    }

    public function test__ice_candidate__dispatches_broadcast_event()
    {
        $recipient_id = User::factory()->create()->id;
        $ice = ['foo' => 'bar'];
        $examCode = ExamSession::factory()->create()->code;

        $this->actingAs($this->user)->json('post', 'api/signalling/ice-candidate', [
            'recipient_id' => $recipient_id,
            'ice' => $ice,
            'exam_code' => $examCode
        ]);

        Event::assertDispatched(
            function (PeerConnectionICE $event) use ($examCode, $ice, $recipient_id) {
                return $event->recipientId === $recipient_id
                    && $event->senderId === $this->user->id
                    && $event->ice === $ice
                    && $event->examCode === $examCode;
            }
        );
    }
}
