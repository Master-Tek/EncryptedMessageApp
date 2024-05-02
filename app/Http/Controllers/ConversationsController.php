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

    public function new()
    {
        $recipients = User::select('id', 'name')
                     ->where('id', '!=', auth()->id())
                     ->get();
        return view('conversations.new', ['recipients' => $recipients, 'currentUserId' => auth()->id()]);
    }

    public function show(Request $request)
    {
        $currentUserId = auth()->id();
        $recipientId = $request->route('id');

        // Check if there is any conversation between the current user and $recipientId
        if (!Message::conversationExists($currentUserId, $recipientId)) {
            return Redirect::route('conversations.new');
        }

        Message::where('receiver_id', $currentUserId)
           ->where('sender_id', $recipientId)
           ->whereNull('read_at')
           ->update(['read_at' => Carbon::now()]);
        
        return view('conversations.show', ['recipientId' => $recipientId]);
    }

    public function exists(Request $request)
    {
        $currentUserId = auth()->id();
        $recipientId = $request->route('id');

        return response()->json([
            'exists' => Message::conversationExists($currentUserId, $recipientId),
        ]);
    }
}
