<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsCorrectMessages()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        // Create messages between users
        $message1 = Message::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
        ]);

        $message2 = Message::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $user->id,
        ]);

        // Create a third message that should not be included in the response
        $message3 = Message::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => User::factory()->create()->id,  // Different recipient
        ]);

        // Make a GET request to the route handling the 'index' method
        $response = $this->json('GET', route('messages.index'), ['recipient_id' => $otherUser->id]);

        // Assert status code 200 and correct data
        $response->assertStatus(200)
                 ->assertJson([
                    ['id' => $message2->id, 'sender_id' => $otherUser->id, 'receiver_id' => $user->id],
                    ['id' => $message1->id, 'sender_id' => $user->id, 'receiver_id' => $otherUser->id]
                 ])
                 ->assertJsonMissing([
                     'id' => $message3->id
                 ]);
    }

    public function testCreateMessage()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($sender);

        $content = 'Hello, this is a test message';
        $response = $this->json('POST', route('messages.create'), [
            'content' => $content,
            'receiver_id' => $receiver->id
        ]);

        // Retrieve the message from the database
        $message = Message::where('sender_id', $sender->id)
                          ->where('receiver_id', $receiver->id)
                          ->first();

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                 ]);

        $this->assertEquals($content, $message->content);
    }

    public function testSoftDeleteMessageSuccess()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        $this->actingAs($sender);

        $response = $this->json('DELETE', route('messages.softDelete', ['id' => $message->id]));

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'recipient_id' => $message->receiver_id,
                     'exists' => false, // assuming conversationExists checks for soft-deleted messages
                 ]);


        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    public function testSoftDeleteMessageNotFound()
    {
        $sender = User::factory()->create();
        $this->actingAs($sender);

        $nonExistingId = 999; // Assuming this ID does not exist
        $response = $this->json('DELETE', route('messages.softDelete', ['id' => $nonExistingId]));

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => 'failed',
                 ]);
    }
}
