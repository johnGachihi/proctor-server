<?php

namespace Tests\Feature;

use App\Events\SignallingMessageSent;
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
            'recipient_id' => 'The recipient id field is required',
            'signalling_message' => 'The signalling message field is required'
        ]);
    }

    public function test__offer__validates_recipient_id_must_be_of_proctor()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/offer', [
                'recipient_id' => 2,
            ]);

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The recipient should be a proctor'
        ]);
    }

    public function test__offer__with_valid_request_params()
    {
        $response = $this->actingAs($this->user)->json(
            'post', 'api/signalling/offer', $this->valid_offer_request_params);

        $response->assertJsonMissingValidationErrors([
            'recipient_id', 'signalling_message'
        ]);
        $response->assertOk();
    }

    public function test__offer__dispatches_broadcast_event()
    {
        $this->actingAs($this->user)->json(
            'post', 'api/signalling/offer', $this->valid_offer_request_params);

        Event::assertDispatched(function (SignallingMessageSent $event) {
            return $event->recipient->id === $this->offer_recipient->id
                && $event->sender->id === $this->user->id
                && $event->signallingMessage === $this->signalling_message;
        });
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
            'recipient_id' => 'The recipient id field is required',
            'signalling_message' => 'The signalling message field is required'
        ]);
    }

    public function test__answer__validates_recipient_id_must_be_of_candidate()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/answer', [
                'recipient_id' => 2,
            ]);

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The recipient should be a candidate'
        ]);
    }

    public function test__answer__with_valid_request_params()
    {
        $response = $this->actingAs($this->user)->json(
            'post', 'api/signalling/answer', $this->valid_answer_request_params);

        $response->assertJsonMissingValidationErrors([
            'recipient_id', 'signalling_message'
        ]);
        $response->assertOk();
    }

    public function test__answer__dispatches_broadcast_event()
    {
        $this->actingAs($this->user)
            ->json('post', 'api/signalling/answer', $this->valid_answer_request_params);

        Event::assertDispatched(function (SignallingMessageSent $event) {
            return $event->recipient->id === $this->answer_recipient->id
                && $event->sender->id === $this->user->id
                && $event->signallingMessage === $this->signalling_message;
        });
    }

    public function test__trickleICE__when_unauthenticated()
    {
        $response = $this->json('post', 'api/signalling/trickleice');

        $response->assertUnauthorized();
    }

    public function test__trickleICE__validates_request_param_checks_not_null()
    {
        $response = $this->actingAs($this->user)
            ->json('post', 'api/signalling/trickleice');

        $response->assertJsonValidationErrors([
            'recipient_id' => 'The recipient id field is required',
            'signalling_message' => 'The signalling message field is required'
        ]);
    }

    public function test__trickleice__with_valid_request_params()
    {
        $response = $this->actingAs($this->user)->json(
            'post', 'api/signalling/trickleice',
            $this->valid_answer_request_params);

        $response->assertJsonMissingValidationErrors([
            'recipient_id', 'signalling_message'
        ]);
        $response->assertOk();
    }

    public function test__trickleICE__dispatches_broadcast_event()
    {
        $this->actingAs($this->user)
            ->json('post', 'api/signalling/trickleice',
                $this->valid_answer_request_params);

        Event::assertDispatched(function (SignallingMessageSent $event) {
            return $event->recipient->id === $this->answer_recipient->id
                && $event->sender->id === $this->user->id
                && $event->signallingMessage === $this->signalling_message;
        });
    }
}
