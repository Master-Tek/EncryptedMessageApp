<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;

class ConversationsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexView()
    {
        // Create a user and set as the currently authenticated user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some sample messages involving the user
        Message::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => User::factory()->create()->id,
            'read_at' => null
        ]);
        Message::factory()->withRead()->create([
            'sender_id' => $user->id,
            'receiver_id' => User::factory()->create()->id,
            'read_at' => null
        ]);
        Message::factory()->create([
            'sender_id' => User::factory()->create()->id,
            'receiver_id' => $user->id,
            'read_at' => null
        ]);

        // Call the index method
        $response = $this->get(route('conversations.index'));

        // Check that the correct view was returned
        $response->assertStatus(200);
        $response->assertViewIs('conversations.index');
        $response->assertViewHas('conversations');
        $response->assertViewHas('currentUserId', $user->id);

        // Optional: Check contents of conversations
        $conversations = $response->viewData('conversations');
        $this->assertEquals(3, $conversations->count()); // Expecting two conversations
        $this->assertEquals(1, $conversations->last()->unread_count);
    }

    public function testNewMethod()
    {
        // Create multiple users
        $user = User::factory()->create();
        $otherUsers = User::factory()->count(3)->create();

        // Authenticate as the first user
        $this->actingAs($user);

        // Make a GET request to the route that triggers the 'new' method
        $response = $this->get(route('conversations.new'));

        // Assert status code 200
        $response->assertStatus(200);

        // Assert that the view is the correct one
        $response->assertViewIs('conversations.new');

        // Assert the data passed to the view
        $response->assertViewHas('recipients', function ($viewRecipients) use ($otherUsers) {
            return $viewRecipients->count() === 3 && $viewRecipients->contains($otherUsers->first());
        });

        $response->assertViewHas('currentUserId', $user->id);
    }

    public function testShowWithNoConversation()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('conversations.show', ['id' => $otherUser->id]));

        $response->assertRedirect(route('conversations.new'));
    }

    public function testShowWithConversationAndUpdateReadAt()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        // Simulate existing conversation
        $message = Message::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $user->id,
            'read_at' => null
        ]);

        $response = $this->get(route('conversations.show', ['id' => $otherUser->id]));

        $response->assertOk();
        $response->assertViewIs('conversations.show');
        $response->assertViewHas('recipientId', $otherUser->id);

        // Check if the read_at attribute is updated
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'read_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function testConversationExists()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        // Simulating that a conversation exists
        Message::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id
        ]);

        $response = $this->getJson(route('conversations.exists', ['id' => $otherUser->id]));

        $response->assertStatus(200)
                 ->assertJson([
                     'exists' => true,
                 ]);
    }

    public function testConversationDoesNotExist()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('conversations.exists', ['id' => $otherUser->id]));

        $response->assertStatus(200)
                 ->assertJson([
                     'exists' => false,
                 ]);
    }
}

