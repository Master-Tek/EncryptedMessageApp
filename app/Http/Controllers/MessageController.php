<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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

    public function createMessage(Request $request)
    {
             
        $message = new Message();
        $message->content = $request->input('content');
        $message->sender_id = auth()->id(); // Get current user ID
        $message->receiver_id = $request->input('receiver_id'); // Receiver ID
        $message->save();

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Soft delete a specific message.
     *
     * @param  \Illuminate\Http\Request  $request
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

