<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class ConversationsController extends Controller
{
    /**
     * Retrieves and displays a list of conversations for the current user.
     *
     * This method queries the Message model to find all conversations involving the current user,
     * identified either as the sender or receiver. It retrieves the last message ID and counterpart user ID for each conversation,
     * groups the messages by counterpart ID, and calculates unread message counts. The data is then passed to the view
     * for presentation.
     *
     * @return \Illuminate\View\View Returns a view with the conversations list and the current user ID.
     */
    public function index()
    {
        $currentUserId = auth()->id();

        $conversations = Message::select(
                DB::raw('MAX(id) as last_message_id'),
                DB::raw("CASE WHEN sender_id = $currentUserId THEN receiver_id ELSE sender_id END as counterpart_id")
            )
            ->where(function ($query) use ($currentUserId) {
                $query->where('sender_id', $currentUserId)
                    ->orWhere('receiver_id', $currentUserId);
            })
            ->groupBy(DB::raw("CASE WHEN sender_id = $currentUserId THEN receiver_id ELSE sender_id END"))
            ->with(['lastMessage' => function($query) {
                $query->addSelect('id', 'sender_id', 'receiver_id', 'content', 'created_at', 'read_at');
            }])
            ->get();

        // Fetch unread counts separately
        $unreadCounts = Message::select(DB::raw('COUNT(*) as unread_count'), DB::raw("CASE WHEN sender_id = $currentUserId THEN receiver_id ELSE sender_id END as counterpart_id"))
            ->where('receiver_id', $currentUserId)
            ->whereNull('read_at')
            ->groupBy(DB::raw("CASE WHEN sender_id = $currentUserId THEN receiver_id ELSE sender_id END"))
            ->pluck('unread_count', 'counterpart_id');

        // Attach unread counts to conversations
        foreach ($conversations as $conversation) {
            $conversation->unread_count = $unreadCounts[$conversation->counterpart_id] ?? 0;
        }

        return view('conversations.index', ['conversations' => $conversations, 'currentUserId' => $currentUserId]);
    }

    /**
     * Displays the view for creating a new conversation.
     *
     * Retrieves and lists all users except the currently authenticated user as potential recipients
     * for a new message. This method prepares the data needed to populate the form where a user
     * can select another user to start a conversation.
     *
     * @return \Illuminate\View\View Returns a view with the list of potential message recipients and
     *         the ID of the current user.
     */
    public function new()
    {
        $recipients = User::select('id', 'name')
                     ->where('id', '!=', auth()->id())
                     ->get();
        return view('conversations.new', ['recipients' => $recipients, 'currentUserId' => auth()->id()]);
    }

    /**
     * Displays a specific conversation between the authenticated user and a selected recipient.
     *
     * This method first checks if a conversation exists between the current authenticated user
     * and the recipient identified by the 'id' parameter in the request. If no conversation exists,
     * it redirects the user to a new conversation creation page. Otherwise, it marks any unread
     * messages in the conversation as read and displays the conversation.
     *
     * @param  \Illuminate\Http\Request  $request  The request object, containing the 'id' of the recipient.
     * @return \Illuminate\Http\Response           Returns a view with the conversation details or
     *                                             redirects to the new conversation page if no existing
     *                                             conversation is found.
     */
    public function show(Request $request)
    {
        $currentUserId = auth()->id();
        $recipient = User::find($request->route('id'));

        // Check if there is any conversation between the current user and $recipientId
        if (!Message::conversationExists($currentUserId, $recipient->id)) {
            return Redirect::route('conversations.new');
        }

        Message::where('receiver_id', $currentUserId)
           ->where('sender_id', $recipient->id)
           ->whereNull('read_at')
           ->update(['read_at' => Carbon::now()]);
        
        return view('conversations.show', ['recipient' => $recipient]);
    }

    /**
     * Checks if a conversation exists between the authenticated user and the specified recipient.
     *
     * This method retrieves the current user's ID and the recipient's ID from the request,
     * and checks if there is any conversation between these two users. The result is returned
     * as a JSON response indicating whether the conversation exists.
     *
     * @param  \Illuminate\Http\Request  $request  The request object, which should contain the recipient's ID as part of the route.
     * @return \Illuminate\Http\JsonResponse       Returns a JSON response with a boolean indicating if the conversation exists.
     */
    public function exists(Request $request)
    {
        $currentUserId = auth()->id();
        $recipientId = $request->route('id');

        return response()->json([
            'exists' => Message::conversationExists($currentUserId, $recipientId),
        ]);
    }
}
