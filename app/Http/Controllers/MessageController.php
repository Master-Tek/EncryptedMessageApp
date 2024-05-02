<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Get messages between the authenticated user and another user.
     *
     * @param  \Illuminate\Http\Request
     *         - 'receiver_id': int, the another user.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $currentUserId = auth()->id();
        $recipientId = $request->input('recipient_id'); // Get selected user ID

        // Retrieve messages where the authenticated user is either the sender or receiver
        $messages = Message::where(function ($query) use ($currentUserId, $recipientId) {
            $query->where(function ($q) use ($currentUserId, $recipientId) {
                $q->where('sender_id', $currentUserId)
                ->where('receiver_id', $recipientId);
            })->orWhere(function ($q) use ($currentUserId, $recipientId) {
                $q->where('sender_id', $recipientId)
                ->where('receiver_id', $currentUserId);
            });
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json($messages);
    }

    /**
     * Creates a new message and saves it to the database.
     *
     * @param  \Illuminate\Http\Request
     *         - 'content': string, the text content of the message.
     *         - 'receiver_id': int, the identifier of the user who is to receive the message.
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMessage(Request $request)
    {
        $message = new Message();
        $message->content = $request->input('content');
        $message->sender_id = auth()->id(); // Assumes the sender is the currently authenticated user
        $message->receiver_id = $request->input('receiver_id'); // Receiver ID from the request
        $message->save();

        return response()->json([
            'status' => 'success',
            'message_id' => $message->id,
            'content' => $message->content,
        ], 200);
    }

    /**
     * Soft delete a specific message.
     *
     * @param  int  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function softDelete($id){
        $message = Message::find($id);
    
        if ($message) {
            $message->delete();
            return response()->json([
                'status' => 'success',
                'recipient_id' => $message->receiver_id,
                'exists' => Message::conversationExists($message->sender_id, $message->receiver_id),
            ], 200);
        } else {
            return response()->json([
                'status' => 'failed',
            ], 404);
        }
    }
}

