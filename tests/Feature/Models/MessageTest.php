<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class MessageTest extends TestCase
{

    /**
     * Test conversationExists method when conversation exists.
     *
     * @return void
     */
    public function testConversationExists()
    {

        $currentUser = User::factory()->create();
        $recipient = User::factory()->create();

        // Simulate conversation existence
        Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $recipient->id,
            'content' => 'Test message',
        ]);

        // Assert that conversation exists
        $exists = Message::conversationExists($currentUser->id, $recipient->id);
        $this->assertTrue($exists);
    }

    /**
     * Test conversationExists method when conversation does not exist.
     *
     * @return void
     */
    public function testConversationDoesNotExist()
    {
        // Create test data
        $currentUserId = 1;
        $recipientId = 3; // Different recipient ID

        // Assert that conversation does not exist
        $exists = Message::conversationExists($currentUserId, $recipientId);
        $this->assertFalse($exists);
    }

    /**
     * Test the encryption of the message content.
     *
     * @return void
     */
    public function testEncryptsContentAttribute()
    {
        $message = new Message();
        $message->setContentAttribute('Test message');

        $encryptedContent = $message->getAttributes()['content'];

        $this->assertNotEquals('Test message', $encryptedContent);
        $this->assertEquals('Test message', Crypt::decryptString($encryptedContent));
    }

    /**
     * Test the decryption of the message content.
     *
     * @return void
     */
    public function testDecryptsContentAttribute()
    {
        $message = new Message();
        $encryptedValue = Crypt::encryptString('Test message');
        
        // Directly setting the encrypted value to simulate fetching from the database
        $decryptedContent = $message->getContentAttribute($encryptedValue);

        $this->assertEquals('Test message', $decryptedContent);
    }
}
